{% extends "layout/base.php" %}

{% block title %}Proforma Q-{{ "%06d"|format(quote_id) }}{% endblock %}

{% block content %}
<div class="proforma-container" style="padding-top: 40px; padding-bottom: 60px;">
    
    <!-- Botón Volver -->
    <div style="margin-bottom: 24px;">
        <a href="{{ url_for('client.dashboard') }}" class="btn-secondary" style="text-decoration: none;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Volver al Catálogo</span>
        </a>
    </div>

    <!-- Documento Proforma -->
    <div class="glass-panel" style="padding: 40px; border-radius: 12px; margin-bottom: 30px; background: rgba(30, 41, 59, 0.4);">
        <div class="proforma-header">
            <div>
                <h1 class="proforma-title" style="font-family: var(--font-heading); font-weight: 800; letter-spacing: -0.01em;">Ferretería Jerusalén</h1>
                <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 4px;">Materiales de Construcción e Ingeniería</p>
            </div>
            <div class="proforma-meta">
                <div style="font-weight: 700; color: var(--text-primary); font-size: 1.1rem; font-family: var(--font-heading);">PROFORMA Q-{{ "%06d"|format(quote_id) }}</div>
                <div style="margin-top: 4px; font-size: 0.85rem;">Fecha: {{ fecha.strftime('%d/%m/%Y %H:%M') }}</div>
                <div style="margin-top: 2px; font-size: 0.85rem; color: var(--accent-amber); font-weight: 600;">Asistencia {{ tipo }}</div>
            </div>
        </div>

        <!-- Información del Cliente -->
        <div style="margin-bottom: 30px;">
            <h3 style="font-size: 1rem; color: var(--text-secondary); border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 6px; margin-bottom: 12px; font-family: var(--font-heading); text-transform: uppercase; letter-spacing: 0.05em;">Información del Cliente</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; font-size: 0.95rem;">
                <div>
                    <span style="color: var(--text-secondary); display: block; font-size: 0.8rem;">Nombre Completo</span>
                    <strong style="color: var(--text-primary);">{{ cliente[0] }}</strong>
                </div>
                <div>
                    <span style="color: var(--text-secondary); display: block; font-size: 0.8rem;">Cédula de Identidad (CI)</span>
                    <strong style="color: var(--text-primary);">{{ cliente[1] }}</strong>
                </div>
                <div>
                    <span style="color: var(--text-secondary); display: block; font-size: 0.8rem;">Teléfono de Contacto</span>
                    <strong style="color: var(--text-primary);">{{ cliente[2] }}</strong>
                </div>
                <div>
                    <span style="color: var(--text-secondary); display: block; font-size: 0.8rem;">Dirección de Entrega</span>
                    <strong style="color: var(--text-primary);">{{ cliente[3] }}</strong>
                </div>
            </div>
        </div>

        <!-- Parámetros Técnicos de la Obra si es IA -->
        {% if tipo == 'IA' and inputs %}
        <div class="glass-panel" style="background: rgba(245, 158, 11, 0.04); border-color: rgba(245, 158, 11, 0.15); padding: 20px; border-radius: 8px; margin-bottom: 30px;">
            <h3 style="font-size: 0.95rem; color: var(--accent-amber); margin-bottom: 12px; font-family: var(--font-heading); text-transform: uppercase; letter-spacing: 0.05em;">Parámetros Técnicos de la Obra (Estimación IA)</h3>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; font-size: 0.9rem;">
                <div>
                    <span style="color: var(--text-secondary); display: block; font-size: 0.75rem;">Dimensión Obra</span>
                    <strong>{{ inputs['m2'] }} m²</strong>
                </div>
                <div>
                    <span style="color: var(--text-secondary); display: block; font-size: 0.75rem;">Estructura</span>
                    <strong>{% if inputs['tipo_construccion'] == 'plantas_altas' %}Con Losas (Plantas Altas){% else %}Simple (Un Piso){% endif %}</strong>
                </div>
                <div>
                    <span style="color: var(--text-secondary); display: block; font-size: 0.75rem;">Clima de Zona</span>
                    <strong>{{ inputs['clima']|capitalize }}</strong>
                </div>
                <div>
                    <span style="color: var(--text-secondary); display: block; font-size: 0.75rem;">Tipo de Suelo</span>
                    <strong>{% if inputs['suelo'] == 'arcilloso' %}Arcilloso (Inestable){% else %}Arenoso (Firme){% endif %}</strong>
                </div>
                <div>
                    <span style="color: var(--text-secondary); display: block; font-size: 0.75rem;">Vigas / Columnas</span>
                    <strong>{{ inputs['vigas']|capitalize }}</strong>
                </div>
                <div>
                    <span style="color: var(--text-secondary); display: block; font-size: 0.75rem;">Ladrillo de Muro</span>
                    <strong>{% if inputs['ladrillo'] == 'gambote' %}Gambote Macizo{% else %}6 Huecos{% endif %}</strong>
                </div>
            </div>
        </div>
        {% endif %}

        <!-- Detalle de la Tabla de Materiales -->
        <div>
            <h3 style="font-size: 1rem; color: var(--text-secondary); border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 6px; margin-bottom: 12px; font-family: var(--font-heading); text-transform: uppercase; letter-spacing: 0.05em;">Materiales Cotizados</h3>
            <table class="proforma-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">No.</th>
                        <th>Material</th>
                        <th style="text-align: right; width: 100px;">Cantidad</th>
                        <th style="text-align: right; width: 150px;">P. Unitario</th>
                        <th style="text-align: right; width: 150px;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    {% for item in items %}
                    <tr>
                        <td>{{ loop.index }}</td>
                        <td><strong>{{ item['nombre_producto'] }}</strong></td>
                        <td style="text-align: right;">{{ item['cantidad'] }}</td>
                        <td style="text-align: right;">{{ "%.2f"|format(item['precio_unitario']) }} Bs.</td>
                        <td style="text-align: right; font-weight: 600; color: var(--text-primary);">{{ "%.2f"|format(item['subtotal']) }} Bs.</td>
                    </tr>
                    {% endfor %}
                    <tr style="background: rgba(255,255,255,0.01);">
                        <td colspan="3"></td>
                        <td style="text-align: right; font-weight: 700; color: var(--text-secondary); border-top: 1px solid rgba(255,255,255,0.1);">TOTAL:</td>
                        <td style="text-align: right; font-weight: 800; color: var(--accent-amber); font-size: 1.2rem; border-top: 1px solid rgba(255,255,255,0.1);">{{ "%.2f"|format(total) }} Bs.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Acciones -->
    <div style="display: flex; gap: 15px; justify-content: flex-end;">
        <a href="{{ url_for('client.download_pdf', id_cotizacion=quote_id) }}" class="btn-secondary" style="text-decoration: none;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <span>Descargar PDF</span>
        </a>
        <button type="button" class="btn-primary" onclick="sendWhatsApp()" style="background: linear-gradient(135deg, #25D366 0%, #128C7E 100%); box-shadow: 0 4px 16px rgba(37, 211, 102, 0.25);">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.458L0 24zm6.59-4.846c1.6.95 2.502 1.48 4.22 1.48 5.535 0 10.04-4.502 10.043-10.04.002-2.684-1.038-5.207-2.93-7.101C16.03 1.597 13.514.557 10.833.557c-5.54 0-10.046 4.503-10.05 10.043-.002 1.83.473 3.614 1.38 5.196L1.13 21.87l6.23-1.63c.48.26.82.41 1.28.48zm12.39-7.15c-.27-.13-1.6-.79-1.85-.88-.25-.09-.43-.13-.62.13-.18.27-.71.88-.88 1.08-.17.2-.34.22-.61.09-.27-.13-1.14-.42-2.18-1.34-.8-.71-1.34-1.6-1.5-1.87-.16-.27-.02-.42.12-.55.12-.12.27-.3.4-.46.13-.16.18-.27.27-.45.09-.18.04-.34-.02-.48-.06-.13-.62-1.5-.85-2.05-.23-.53-.46-.45-.63-.46-.16 0-.34-.01-.52-.01-.19 0-.49.07-.75.34-.26.27-1 1-1 2.45s1 2.85 1.14 3.03c.14.18 1.95 2.98 4.74 4.18.66.29 1.18.46 1.58.59.67.21 1.28.18 1.76.11.54-.08 1.6-.66 1.83-1.29.23-.62.23-1.15.16-1.28-.06-.13-.24-.22-.51-.35z"/></svg>
            <span>Enviar Cotización al Administrador</span>
        </button>
    </div>
</div>

<script>
    function sendWhatsApp() {
        const phone = "{{ config.WHATSAPP_ADMIN_PHONE }}";
        const tipo = "{{ tipo }}";
        const quoteId = 'Q-{{ "%06d"|format(quote_id) }}';
        const clienteNombre = "{{ cliente[0] }}";
        const clienteCI = "{{ cliente[1] }}";
        const total = "{{ '%.2f'|format(total) }}";
        
        let text = '';
        
        if (tipo === 'IA') {
            const m2 = "{{ inputs['m2'] if inputs else '' }}";
            const constType = "{{ 'Con losas (Plantas Altas)' if inputs and inputs['tipo_construccion'] == 'plantas_altas' else 'Simple (Un Piso)' }}";
            const clima = "{{ inputs['clima']|capitalize if inputs else '' }}";
            const suelo = "{{ 'Arcilloso (Inestable)' if inputs and inputs['suelo'] == 'arcilloso' else 'Arenoso (Firme)' }}";
            const vigas = "{{ inputs['vigas']|capitalize if inputs else '' }}";
            const ladrillo = "{{ 'Gambote Macizo' if inputs and inputs['ladrillo'] == 'gambote' else '6 Huecos' }}";

            text += `*FERRETERÍA JERUSALÉN - COTIZACIÓN TÉCNICA IA*\n`;
            text += `*Nro:* ${quoteId}\n`;
            text += `*Cliente:* ${clienteNombre}\n`;
            text += `*CI:* ${clienteCI}\n\n`;
            text += `*Especificaciones Técnicas de Obra:*\n`;
            text += `- Área: ${m2} m²\n`;
            text += `- Estructura: ${constType}\n`;
            text += `- Clima: ${clima}\n`;
            text += `- Suelo: ${suelo}\n`;
            text += `- Vigas/Columnas: ${vigas}\n`;
            text += `- Ladrillo Preferido: ${ladrillo}\n\n`;
            text += `*Materiales Estimados por IA:*\n`;
        } else {
            text += `*FERRETERÍA JERUSALÉN - COTIZACIÓN TRADICIONAL*\n`;
            text += `*Nro:* ${quoteId}\n`;
            text += `*Cliente:* ${clienteNombre}\n`;
            text += `*CI:* ${clienteCI}\n\n`;
            text += `*Detalle de Materiales Cotizados:*\n`;
        }
        
        {% for item in items %}
        text += `{{ loop.index }}. *{{ item['nombre_producto'] }}* - {{ item['cantidad'] }} u. x {{ '%.2f'|format(item['precio_unitario']) }} Bs. = {{ '%.2f'|format(item['subtotal']) }} Bs.\n`;
        {% endfor %}
        
        text += `\n*TOTAL COTIZADO:* ${total} Bs.\n\n`;
        text += `_Por favor, coordinar la entrega y el método de pago (Efectivo/QR/Transferencia)._`;
        
        const url = `https://api.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(text)}`;
        window.open(url, '_blank');
    }
</script>
{% endblock %}
