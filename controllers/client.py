import json
from io import BytesIO
from flask import Blueprint, render_template, request, session, redirect, url_for, flash, send_file, current_app, abort
from database import get_db
from models.producto import get_todos_productos
from models.venta import guardar_cotizacion, get_cotizacion_por_id, get_historial_cotizaciones_cliente

client_bp = Blueprint('client', __name__)

@client_bp.before_request
def check_client():
    if 'id_usuario' not in session:
        return redirect(url_for('auth.login'))
    if session.get('rol_name') != 'Cliente':
        return redirect(url_for('admin.dashboard'))

@client_bp.route('/client/dashboard')
def dashboard():
    conn = get_db()
    cur = conn.cursor()
    try:
        # Obtener catálogo de productos del modelo
        productos = get_todos_productos(cur)
        
        # Obtener historial de cotizaciones del modelo
        cotizaciones = get_historial_cotizaciones_cliente(cur, session.get('id_cliente'))
        
        # Obtener información del cliente
        cur.execute("""
            SELECT p.nombre_completo, p.ci_nit, p.telefono, p.direccion
            FROM personas p
            JOIN clientes c ON p.id_persona = c.id_persona
            WHERE c.id_cliente = %s;
        """, (session.get('id_cliente'),))
        cliente_info = cur.fetchone()
        
    finally:
        cur.close()
        
    # Renderizar template .php en la carpeta views
    return render_template('dashboard/panel.php', productos=productos, cotizaciones=cotizaciones, cliente_info=cliente_info)

@client_bp.route('/client/quote/generate', methods=['POST'])
def generate_quote():
    cart_data_raw = request.form.get('cart_data', '')
    if not cart_data_raw:
        flash("El carrito está vacío.", "error")
        return redirect(url_for('client.dashboard'))
        
    try:
        cart = json.loads(cart_data_raw)
    except Exception:
        flash("Formato de carrito inválido.", "error")
        return redirect(url_for('client.dashboard'))
        
    if not cart:
        flash("El carrito no contiene productos.", "error")
        return redirect(url_for('client.dashboard'))
        
    conn = get_db()
    cur = conn.cursor()
    try:
        precio_total = 0.0
        items_validados = []
        
        for item in cart:
            id_prod = int(item['id'])
            cantidad = int(item['quantity'])
            
            cur.execute("""
                SELECT nombre_producto, precio_venta, stock_actual
                FROM productos
                WHERE id_producto = %s;
            """, (id_prod,))
            prod = cur.fetchone()
            
            if not prod:
                flash(f"Producto con ID {id_prod} no existe en catálogo.", "error")
                return redirect(url_for('client.dashboard'))
                
            nombre, precio, stock = prod
            
            if cantidad > stock:
                flash(f"Stock insuficiente para '{nombre}'. Solicitado: {cantidad}, Disponible: {stock}.", "error")
                return redirect(url_for('client.dashboard'))
                
            subtotal = float(precio) * cantidad
            precio_total += subtotal
            
            items_validados.append({
                'id_producto': id_prod,
                'nombre_producto': nombre,
                'cantidad': cantidad,
                'precio_unitario': float(precio),
                'subtotal': subtotal
            })
            
        # Inserción usando el modelo
        id_cliente = session.get('id_cliente')
        id_cotizacion = guardar_cotizacion(conn, cur, id_cliente, 'Tradicional', precio_total, items_validados)
        
        flash("¡Cotización generada correctamente!", "success")
        return redirect(url_for('client.view_quote', id_cotizacion=id_cotizacion))
        
    except Exception as e:
        conn.rollback()
        flash(f"Error al guardar cotización: {str(e)}", "error")
        return redirect(url_for('client.dashboard'))
    finally:
        cur.close()

@client_bp.route('/client/quote/ia_calculate', methods=['POST'])
def ia_calculate():
    flash("El asistente consultor de obras con IA está temporalmente en desarrollo y se activará en próximas etapas.", "info")
    return redirect(url_for('client.dashboard'))

@client_bp.route('/client/quote/<int:id_cotizacion>')
def view_quote(id_cotizacion):
    conn = get_db()
    cur = conn.cursor()
    try:
        # Obtener cotización usando el modelo
        quote = get_cotizacion_por_id(cur, id_cotizacion, session.get('id_cliente'))
        
        if not quote:
            flash("Cotización no encontrada o no autorizada.", "error")
            return redirect(url_for('client.dashboard'))
            
        id_cotiz, tipo, total, fecha, detalle_raw = quote
        
        if isinstance(detalle_raw, str):
            detalle = json.loads(detalle_raw)
        else:
            detalle = detalle_raw
            
        if tipo == 'IA':
            items = detalle.get('items', [])
            inputs = detalle.get('inputs', {})
        else:
            items = detalle
            inputs = None
            
        cur.execute("""
            SELECT p.nombre_completo, p.ci_nit, p.telefono, p.direccion
            FROM personas p
            JOIN clientes c ON p.id_persona = c.id_persona
            WHERE c.id_cliente = %s;
        """, (session.get('id_cliente'),))
        cliente = cur.fetchone()
        
    finally:
        cur.close()
        
    # Renderizar template .php en la carpeta views
    return render_template('cotizaciones/nueva.php', quote_id=id_cotiz, tipo=tipo, total=total, fecha=fecha, items=items, inputs=inputs, cliente=cliente)

@client_bp.route('/client/quote/<int:id_cotizacion>/pdf')
def download_pdf(id_cotizacion):
    conn = get_db()
    cur = conn.cursor()
    try:
        # Obtener cotización usando el modelo
        quote = get_cotizacion_por_id(cur, id_cotizacion, session.get('id_cliente'))
        
        if not quote:
            abort(404, description="Cotización no encontrada.")
            
        id_cotiz, tipo, total, fecha, detalle_raw = quote
        if isinstance(detalle_raw, str):
            detalle = json.loads(detalle_raw)
        else:
            detalle = detalle_raw
            
        items = detalle.get('items', []) if tipo == 'IA' else detalle
        
        cur.execute("""
            SELECT p.nombre_completo, p.ci_nit, p.telefono, p.direccion
            FROM personas p
            JOIN clientes c ON p.id_persona = c.id_persona
            WHERE c.id_cliente = %s;
        """, (session.get('id_cliente'),))
        cliente = cur.fetchone()
        
    finally:
        cur.close()
        
    from reportlab.lib.pagesizes import letter
    from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle
    from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
    from reportlab.lib import colors
    
    buffer = BytesIO()
    doc = SimpleDocTemplate(buffer, pagesize=letter,
                            rightMargin=40, leftMargin=40, topMargin=40, bottomMargin=40)
    story = []
    
    styles = getSampleStyleSheet()
    
    title_style = ParagraphStyle(
        'DocTitle',
        parent=styles['Heading1'],
        fontSize=20,
        leading=24,
        textColor=colors.HexColor('#ea580c'),
        alignment=1
    )
    
    normal_style = ParagraphStyle(
        'DocText',
        parent=styles['Normal'],
        fontSize=10,
        leading=14,
        textColor=colors.HexColor('#334155')
    )
    
    bold_style = ParagraphStyle(
        'DocTextBold',
        parent=normal_style,
        fontName='Helvetica-Bold'
    )
    
    story.append(Paragraph("FERRETERÍA JERUSALÉN", title_style))
    story.append(Paragraph("Sistemas de Construcción e Ingeniería", ParagraphStyle('Sub', parent=title_style, fontSize=10, textColor=colors.HexColor('#64748b'), leading=12)))
    story.append(Spacer(1, 15))
    
    meta_data = [
        [Paragraph("<b>Nro. Cotización:</b>", normal_style), Paragraph(f"Q-{id_cotiz:06d}", bold_style), 
         Paragraph("<b>Fecha:</b>", normal_style), Paragraph(fecha.strftime('%d/%m/%Y %H:%M'), normal_style)],
        [Paragraph("<b>Tipo Cotización:</b>", normal_style), Paragraph(f"Asistencia {tipo}", bold_style),
         Paragraph("<b>Validez:</b>", normal_style), Paragraph("15 días", normal_style)]
    ]
    meta_table = Table(meta_data, colWidths=[100, 150, 100, 150])
    meta_table.setStyle(TableStyle([
        ('ALIGN', (0,0), (-1,-1), 'LEFT'),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
        ('BOTTOMPADDING', (0,0), (-1,-1), 4),
    ]))
    story.append(meta_table)
    story.append(Spacer(1, 15))
    
    story.append(Paragraph("<b>INFORMACIÓN DEL CLIENTE</b>", ParagraphStyle('SectionHeader', parent=styles['Heading3'], textColor=colors.HexColor('#1e293b'))))
    story.append(Spacer(1, 5))
    
    client_data = [
        [Paragraph("<b>Nombre Completo:</b>", normal_style), Paragraph(cliente[0], normal_style)],
        [Paragraph("<b>Cédula de Identidad (CI):</b>", normal_style), Paragraph(cliente[1], normal_style)],
        [Paragraph("<b>Teléfono (WhatsApp):</b>", normal_style), Paragraph(cliente[2], normal_style)],
        [Paragraph("<b>Dirección de Domicilio:</b>", normal_style), Paragraph(cliente[3], normal_style)]
    ]
    client_table = Table(client_data, colWidths=[120, 400])
    client_table.setStyle(TableStyle([
        ('ALIGN', (0,0), (-1,-1), 'LEFT'),
        ('GRID', (0,0), (-1,-1), 0.5, colors.HexColor('#cbd5e1')),
        ('TOPPADDING', (0,0), (-1,-1), 6),
        ('BOTTOMPADDING', (0,0), (-1,-1), 6),
        ('LEFTPADDING', (0,0), (-1,-1), 8),
        ('BACKGROUND', (0,0), (0,-1), colors.HexColor('#f8fafc')),
    ]))
    story.append(client_table)
    story.append(Spacer(1, 20))
    
    story.append(Paragraph("<b>DETALLE DE MATERIALES COTIZADOS</b>", ParagraphStyle('SectionHeader2', parent=styles['Heading3'], textColor=colors.HexColor('#1e293b'))))
    story.append(Spacer(1, 5))
    
    table_headers = [
        Paragraph("<b>No.</b>", bold_style),
        Paragraph("<b>Material / Producto</b>", bold_style),
        Paragraph("<b>Cantidad</b>", bold_style),
        Paragraph("<b>Precio Unitario (Bs.)</b>", bold_style),
        Paragraph("<b>Subtotal (Bs.)</b>", bold_style)
    ]
    
    product_table_data = [table_headers]
    for idx, item in enumerate(items):
        product_table_data.append([
            Paragraph(str(idx + 1), normal_style),
            Paragraph(item['nombre_producto'], normal_style),
            Paragraph(str(item['cantidad']), normal_style),
            Paragraph(f"{item['precio_unitario']:.2f}", normal_style),
            Paragraph(f"{item['subtotal']:.2f}", normal_style)
        ])
        
    product_table_data.append([
        "", "", "",
        Paragraph("<b>TOTAL:</b>", bold_style),
        Paragraph(f"<b>{total:.2f} Bs.</b>", bold_style)
    ])
    
    prod_table = Table(product_table_data, colWidths=[30, 240, 70, 90, 90])
    prod_table.setStyle(TableStyle([
        ('ALIGN', (0,0), (-1,-1), 'LEFT'),
        ('ALIGN', (2,0), (-1,-1), 'RIGHT'),
        ('BACKGROUND', (0,0), (-1,0), colors.HexColor('#475569')),
        ('TEXTCOLOR', (0,0), (-1,0), colors.white),
        ('GRID', (0,0), (-1,-2), 0.5, colors.HexColor('#cbd5e1')),
        ('TOPPADDING', (0,0), (-1,-1), 8),
        ('BOTTOMPADDING', (0,0), (-1,-1), 8),
        ('LEFTPADDING', (0,0), (-1,-1), 6),
        ('RIGHTPADDING', (0,0), (-1,-1), 6),
    ]))
    
    header_style_white = ParagraphStyle('HeadWhite', parent=bold_style, textColor=colors.white)
    for c in range(len(table_headers)):
        product_table_data[0][c] = Paragraph(table_headers[c].text, header_style_white)
        
    story.append(prod_table)
    story.append(Spacer(1, 30))
    
    story.append(Paragraph("<b>Términos y Condiciones:</b>", bold_style))
    story.append(Paragraph("1. Los precios indicados en esta proforma están expresados en Bolivianos (Bs.) e incluyen impuestos de ley.", normal_style))
    story.append(Paragraph("2. Esta cotización tiene una validez de 15 días calendario a partir de su emisión.", normal_style))
    story.append(Paragraph("3. Las proformas estimadas mediante el consultor IA son aproximaciones basadas en fórmulas de ingeniería y deben ser corroboradas con el plano físico de la obra.", normal_style))
    
    doc.build(story)
    buffer.seek(0)
    
    return send_file(
        buffer,
        as_attachment=True,
        download_name=f"Proforma_Jerusalen_{id_cotizacion:06d}.pdf",
        mimetype="application/pdf"
    )
