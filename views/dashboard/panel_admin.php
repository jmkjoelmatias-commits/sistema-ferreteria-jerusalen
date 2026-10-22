{% extends "layout/base.php" %}

{% block title %}Panel de Administración{% endblock %}

{% block body_layout %}
<div class="dashboard-layout">
    
    <!-- Barra Lateral (Sidebar) -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon" style="background: linear-gradient(135deg, #ef4444 0%, #ea580c 100%);">A</div>
            <div class="brand-name">Panel Control</div>
        </div>
        
        <div class="sidebar-nav">
            <a href="javascript:void(0)" class="nav-link active" id="nav-usuarios" onclick="switchAdminTab('usuarios')">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span>Gestión de Usuarios</span>
            </a>
            <a href="javascript:void(0)" class="nav-link" id="nav-clientes" onclick="switchAdminTab('clientes')">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span>Clientes Registrados</span>
            </a>
            <a href="javascript:void(0)" class="nav-link" id="nav-inventario" onclick="switchAdminTab('inventario')">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <span>Control Inventario</span>
            </a>
            <a href="javascript:void(0)" class="nav-link" id="nav-crear-cliente" onclick="switchAdminTab('crear-cliente')">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                <span>Crear Cliente</span>
            </a>
            <a href="{{ url_for('auth.logout') }}" class="nav-link" style="margin-top: 20px; color: var(--error);">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span>Cerrar Sesión</span>
            </a>
        </div>
        
        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="user-avatar" style="color: var(--error); border-color: rgba(239, 68, 68, 0.2);">
                    {{ session.get('username')[0].upper() }}
                </div>
                <div class="user-info">
                    <span class="user-name">{{ session.get('username') }}</span>
                    <span class="user-role">{{ session.get('rol_name') }}</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Contenido Principal -->
    <div class="main-content">
        
        <!-- ================= MÓDULO DE USUARIOS ================= -->
        <div id="tab-usuarios-content" class="admin-panel-content active">
            <h2 class="section-title">Control de Cuentas de Acceso</h2>
            <p class="section-desc">Habilite, deshabilite o elimine las credenciales de ingreso de administradores y clientes.</p>
            
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Nombre Completo</th>
                            <th>Rol</th>
                            <th>Teléfono</th>
                            <th>Estado Acceso</th>
                            <th style="text-align: center; width: 220px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {% for user in usuarios %}
                        <tr>
                            <td>{{ user[0] }}</td>
                            <td><strong>{{ user[1] }}</strong></td>
                            <td>{{ user[3] }}</td>
                            <td>
                                <span class="status-badge" style="background: rgba(255,255,255,0.05); color: var(--text-primary); border: 1px solid rgba(255,255,255,0.1);">
                                    {{ user[4] }}
                                </span>
                            </td>
                            <td>{{ user[5] or 'S/T' }}</td>
                            <td>
                                {% if user[2] %}
                                <span class="status-badge active">Activo</span>
                                {% else %}
                                <span class="status-badge inactive">Bloqueado</span>
                                {% endif %}
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                                    <!-- Editar Usuario -->
                                    <button class="toggle-btn enable" style="padding: 6px 10px;" onclick="openEditUserModal({{ user[0] }}, '{{ user[1] }}', '{{ user[3] }}', '{{ user[5] or '' }}', '{{ user[6] or '' }}', {{ user[7] }}, '{{ user[8] }}')">
                                        Editar
                                    </button>

                                    {% if user[0] == session.get('id_usuario') %}
                                    <span style="font-size: 0.8rem; color: var(--text-secondary); font-style: italic;">Sesión Activa</span>
                                    {% else %}
                                        {% if user[2] %}
                                        <a href="{{ url_for('admin.toggle_user', id_usuario=user[0]) }}" class="toggle-btn disable" style="text-decoration: none;">Bloquear</a>
                                        {% else %}
                                        <a href="{{ url_for('admin.toggle_user', id_usuario=user[0]) }}" class="toggle-btn enable" style="text-decoration: none;">Habilitar</a>
                                        {% endif %}
                                        
                                        <a href="{{ url_for('admin.delete_user', id_usuario=user[0]) }}" class="toggle-btn disable" style="text-decoration: none; padding: 6px 8px; background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.3);" onclick="return confirm('¿Está seguro de eliminar esta cuenta? Se eliminará toda su información personal en cascada.')" title="Eliminar cuenta">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </a>
                                    {% endif %}
                                </div>
                            </td>
                        </tr>
                        {% endfor %}
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- ================= MÓDULO DE CLIENTES REGISTRADOS ================= -->
        <div id="tab-clientes-content" class="admin-panel-content">
            <h2 class="section-title">Clientes Registrados</h2>
            <p class="section-desc">Identidades físicas de compradores. Desde aquí puede editar sus datos de contacto o eliminarlos.</p>
            
            <!-- Buscador de Clientes -->
            <div class="search-box">
                <form action="{{ url_for('admin.dashboard') }}" method="GET" id="search-form" style="display: flex; gap: 10px;">
                    <input type="hidden" name="active_tab" value="clientes">
                    <input type="text" name="search_client" class="search-input" placeholder="Buscar por Cédula de Identidad (CI), Teléfono Celular o Nombre..." value="{{ search_query }}">
                    <button type="submit" class="btn-primary" style="width: auto; padding: 0 24px;">Buscar</button>
                    {% if search_query %}
                    <a href="{{ url_for('admin.dashboard') }}" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center;">Limpiar</a>
                    {% endif %}
                </form>
            </div>
            
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID Cliente</th>
                            <th>Nombre Completo</th>
                            <th>Cédula Identidad (CI)</th>
                            <th>Teléfono (WhatsApp)</th>
                            <th>Dirección Domiciliaria</th>
                            <th>Fecha Registro</th>
                            <th style="text-align: center; width: 140px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {% if clientes %}
                            {% for c in clientes %}
                            <tr>
                                <td>C-{{ "%04d"|format(c[0]) }}</td>
                                <td><strong>{{ c[1] }}</strong></td>
                                <td>{{ c[2] }}</td>
                                <td>
                                    <a href="https://api.whatsapp.com/send?phone={{ c[3] }}" target="_blank" style="color: #25D366; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.458L0 24zm6.59-4.846c1.6.95 2.502 1.48 4.22 1.48 5.535 0 10.04-4.502 10.043-10.04.002-2.684-1.038-5.207-2.93-7.101C16.03 1.597 13.514.557 10.833.557c-5.54 0-10.046 4.503-10.05 10.043-.002 1.83.473 3.614 1.38 5.196L1.13 21.87l6.23-1.63c.48.26.82.41 1.28.48zm12.39-7.15c-.27-.13-1.6-.79-1.85-.88-.25-.09-.43-.13-.62.13-.18.27-.71.88-.88 1.08-.17.2-.34.22-.61.09-.27-.13-1.14-.42-2.18-1.34-.8-.71-1.34-1.6-1.5-1.87-.16-.27-.02-.42.12-.55.12-.12.27-.3.4-.46.13-.16.18-.27.27-.45.09-.18.04-.34-.02-.48-.06-.13-.62-1.5-.85-2.05-.23-.53-.46-.45-.63-.46-.16 0-.34-.01-.52-.01-.19 0-.49.07-.75.34-.26.27-1 1-1 2.45s1 2.85 1.14 3.03c.14.18 1.95 2.98 4.74 4.18.66.29 1.18.46 1.58.59.67.21 1.28.18 1.76.11.54-.08 1.6-.66 1.83-1.29.23-.62.23-1.15.16-1.28-.06-.13-.24-.22-.51-.35z"/></svg>
                                        <span>{{ c[3] }}</span>
                                    </a>
                                </td>
                                <td>{{ c[4] }}</td>
                                <td>{{ c[5].strftime('%d/%m/%Y') }}</td>
                                <td style="text-align: center;">
                                    <div style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                                        <!-- Editar Cliente -->
                                        <button class="toggle-btn enable" style="padding: 6px 10px;" onclick="openEditClientModal({{ c[0] }}, '{{ c[1] }}', '{{ c[2] }}', '{{ c[3] }}', '{{ c[4] }}')">
                                            Editar
                                        </button>
                                        <!-- Eliminar Cliente -->
                                        <a href="{{ url_for('admin.delete_client', id_cliente=c[0]) }}" class="toggle-btn disable" style="text-decoration: none; padding: 6px 8px; background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.3);" onclick="return confirm('¿Seguro que desea eliminar a este cliente? Se borrarán sus proformas e historial de compras.')" title="Eliminar Cliente">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            {% endfor %}
                        {% else %}
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 30px;">
                                    Ningún cliente coincide con la búsqueda.
                                </td>
                            </tr>
                        {% endif %}
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- ================= MÓDULO DE INVENTARIO Y ALERTAS ================= -->
        <div id="tab-inventario-content" class="admin-panel-content">
            <h2 class="section-title">Inventario Físico de Materiales</h2>
            <p class="section-desc">Mapeo en tiempo real de productos. Se evalúa automáticamente la regla de Alerta de Stock Mínimo.</p>
            
            <div class="data-table-container" style="margin-bottom: 40px;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Código Barras</th>
                            <th>Nombre Material</th>
                            <th>Categoría</th>
                            <th>Precio Venta</th>
                            <th style="text-align: right;">Stock Mínimo</th>
                            <th style="text-align: right; width: 140px;">Stock Actual</th>
                            <th>Estado Abastecimiento</th>
                            <th style="text-align: center; width: 220px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {% for prod in inventario %}
                        {% set is_low = prod[4] <= prod[5] %}
                        <tr class="{% if is_low %}pulse-alert-red{% endif %}">
                            <td><code>{{ prod[1] }}</code></td>
                            <td><strong style="color: var(--text-primary);">{{ prod[2] }}</strong></td>
                            <td>{{ prod[6] }}</td>
                            <td><strong>{{ prod[3] }} Bs.</strong></td>
                            <td style="text-align: right;">{{ prod[5] }} u.</td>
                            <td style="text-align: right; font-weight: 800; {% if is_low %}color: var(--error);{% endif %}">
                                {{ prod[4] }} u.
                            </td>
                            <td>
                                {% if is_low %}
                                <span class="alert-badge">¡Stock Mínimo! Reabastecer</span>
                                {% else %}
                                <span class="status-badge active" style="font-size: 0.7rem; border-color: rgba(16,185,129,0.15);">Suficiente</span>
                                {% endif %}
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                                    <!-- Editar Stock Rápido -->
                                    <form action="{{ url_for('admin.update_stock') }}" method="POST" style="display: flex; gap: 4px; align-items: center;">
                                        <input type="hidden" name="id_producto" value="{{ prod[0] }}">
                                        <input type="number" name="stock" value="{{ prod[4] }}" class="form-input" style="width: 55px; padding: 4px 6px; text-align: center;" min="0" required>
                                        <button type="submit" class="qty-btn" style="width: 26px; height: 26px; background: rgba(245, 158, 11, 0.1);" title="Actualizar Stock">
                                            ✓
                                        </button>
                                    </form>
                                    
                                    <!-- Editar Completo -->
                                    <button class="toggle-btn enable" style="padding: 6px 10px;" onclick="openEditProductModal({{ prod[0] }}, '{{ prod[2] }}', '{{ prod[1] }}', {{ prod[3] }}, {{ prod[4] }}, {{ prod[5] }}, {{ prod[7] }})">
                                        Editar
                                    </button>
                                    
                                    <!-- Eliminar Producto -->
                                    <a href="{{ url_for('admin.delete_product', id_producto=prod[0]) }}" class="toggle-btn disable" style="text-decoration: none; padding: 6px 8px; background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.3);" onclick="return confirm('¿Seguro que desea eliminar este material del inventario?')" title="Eliminar Insumo">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        {% endfor %}
                    </tbody>
                </table>
            </div>
            
            <!-- SECCIONES DE ABAJO: CREAR PRODUCTO -->
            <div style="border-top: 1px solid rgba(255,255,255,0.05); padding-top: 30px; max-width: 600px; margin: 0 auto;">
                
                <!-- Añadir Nuevo Material -->
                <div class="glass-panel" style="padding: 24px;">
                    <h3 style="font-family: var(--font-heading); font-size: 1.25rem; color: var(--accent-orange); margin-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 8px;">Añadir Material al Inventario</h3>
                    <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 16px;">Ingrese un nuevo producto o material de construcción al catálogo físico de la ferretería.</p>
                    
                    <form action="{{ url_for('admin.create_product') }}" method="POST">
                        <div class="form-group" style="margin-bottom: 12px;">
                            <label for="p_barras" class="form-label" style="font-size: 0.75rem;">Código de Barras</label>
                            <input type="text" name="codigo_barras" id="p_barras" class="form-input" style="padding: 8px 12px;" placeholder="ej. 7401009" required>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 12px;">
                            <label for="p_nombre" class="form-label" style="font-size: 0.75rem;">Nombre Comercial</label>
                            <input type="text" name="nombre_producto" id="p_nombre" class="form-input" style="padding: 8px 12px;" placeholder="ej. Yeso de Construcción (25kg)" required>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 12px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="p_precio" class="form-label" style="font-size: 0.75rem;">Precio (Bs.)</label>
                                <input type="number" step="0.01" name="precio_venta" id="p_precio" class="form-input" style="padding: 8px 12px;" placeholder="Bs." required>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="p_stock" class="form-label" style="font-size: 0.75rem;">Stock Inicial</label>
                                <input type="number" name="stock_actual" id="p_stock" class="form-input" style="padding: 8px 12px;" placeholder="Cant." required min="0">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="p_stock_min" class="form-label" style="font-size: 0.75rem;">Stock Mínimo</label>
                                <input type="number" name="stock_minimo" id="p_stock_min" class="form-input" style="padding: 8px 12px;" placeholder="Mín." required min="0">
                            </div>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="p_cat" class="form-label" style="font-size: 0.75rem;">Categoría del Material</label>
                            <select name="id_categoria" id="p_cat" class="select-input" style="padding: 8px 12px;">
                                {% for cat in categorias %}
                                <option value="{{ cat[0] }}">{{ cat[1] }}</option>
                                {% endfor %}
                            </select>
                        </div>
                        
                        <button type="submit" class="btn-primary" style="padding: 10px 16px;">
                            <span>Añadir Producto</span>
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ================= MÓDULO DE CREAR CLIENTE ================= -->
        <div id="tab-crear-cliente-content" class="admin-panel-content">
            <h2 class="section-title">Registrar Nuevo Cliente</h2>
            <p class="section-desc">Cree un nuevo perfil físico de cliente y su cuenta de acceso unificada en base de datos.</p>
            
            <div style="max-width: 600px; margin: 0 auto;">
                <div class="glass-panel" style="padding: 30px;">
                    <form action="{{ url_for('admin.create_client') }}" method="POST">
                        <div class="form-group" style="margin-bottom: 12px;">
                            <label for="c_ci" class="form-label" style="font-size: 0.75rem;">Cédula de Identidad (CI)</label>
                            <input type="text" name="ci_nit" id="c_ci" class="form-input" style="padding: 8px 12px;" placeholder="ej. 1234567" required>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 12px;">
                            <label for="c_nombre" class="form-label" style="font-size: 0.75rem;">Nombre Completo</label>
                            <input type="text" name="nombre_completo" id="c_nombre" class="form-input" style="padding: 8px 12px;" placeholder="ej. Juan Perez" required>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 12px;">
                            <label for="c_telefono" class="form-label" style="font-size: 0.75rem;">Celular (WhatsApp)</label>
                            <input type="text" name="telefono" id="c_telefono" class="form-input" style="padding: 8px 12px;" placeholder="ej. 76543210" required>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 12px;">
                            <label for="c_direccion" class="form-label" style="font-size: 0.75rem;">Dirección Domiciliaria</label>
                            <input type="text" name="direccion" id="c_direccion" class="form-input" style="padding: 8px 12px;" placeholder="ej. Calle Cívica Nro. 123" required>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="c_user" class="form-label" style="font-size: 0.75rem;">Usuario Acceso</label>
                                <input type="text" name="username" id="c_user" class="form-input" style="padding: 8px 12px;" placeholder="ej. juan_perez" required autocomplete="off">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="c_pass" class="form-label" style="font-size: 0.75rem;">Contraseña</label>
                                <input type="password" name="password" id="c_pass" class="form-input" style="padding: 8px 12px;" placeholder="Clave" required autocomplete="new-password">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-primary" style="padding: 10px 16px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 4px 16px rgba(16, 185, 129, 0.25);">
                            <span>Registrar Cliente</span>
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
    </div>
</div>

<!-- MODAL EDICIÓN DE PRODUCTO -->
<div id="edit-product-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; justify-content: center; align-items: center; padding: 20px; backdrop-filter: blur(5px);">
    <div class="glass-panel" style="width: 100%; max-width: 450px; padding: 30px; background: #0f172a; border-color: rgba(245, 158, 11, 0.2);">
        <h3 style="font-family: var(--font-heading); font-size: 1.25rem; color: var(--accent-orange); margin-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 8px;">
            Editar Producto del Catálogo
        </h3>
        
        <form id="edit-product-form" action="" method="POST">
            <div class="form-group">
                <label for="edit_p_codigo" class="form-label">Código de Barras</label>
                <input type="text" name="codigo_barras" id="edit_p_codigo" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="edit_p_nombre" class="form-label">Nombre del Producto</label>
                <input type="text" name="nombre_producto" id="edit_p_nombre" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="edit_p_precio" class="form-label">Precio de Venta (Bs.)</label>
                <input type="number" step="0.01" name="precio_venta" id="edit_p_precio" class="form-input" required>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_p_stock" class="form-label">Stock Actual</label>
                    <input type="number" name="stock_actual" id="edit_p_stock" class="form-input" required min="0">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_p_stock_min" class="form-label">Stock Mínimo</label>
                    <input type="number" name="stock_minimo" id="edit_p_stock_min" class="form-input" required min="0">
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 24px;">
                <label for="edit_p_cat" class="form-label">Categoría del Material</label>
                <select name="id_categoria" id="edit_p_cat" class="select-input">
                    {% for cat in categorias %}
                    <option value="{{ cat[0] }}">{{ cat[1] }}</option>
                    {% endfor %}
                </select>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn-secondary" onclick="closeEditProductModal()">Cancelar</button>
                <button type="submit" class="btn-primary" style="width: auto;">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDICIÓN DE CLIENTE -->
<div id="edit-client-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; justify-content: center; align-items: center; padding: 20px; backdrop-filter: blur(5px);">
    <div class="glass-panel" style="width: 100%; max-width: 450px; padding: 30px; background: #0f172a; border-color: rgba(16, 185, 129, 0.2);">
        <h3 style="font-family: var(--font-heading); font-size: 1.25rem; color: #10b981; margin-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 8px;">
            Editar Datos del Cliente
        </h3>
        
        <form id="edit-client-form" action="" method="POST">
            <div class="form-group">
                <label for="edit_c_nombre" class="form-label">Nombre Completo</label>
                <input type="text" name="nombre_completo" id="edit_c_nombre" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="edit_c_ci" class="form-label">Cédula de Identidad (CI)</label>
                <input type="text" name="ci_nit" id="edit_c_ci" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="edit_c_telefono" class="form-label">Teléfono (WhatsApp)</label>
                <input type="text" name="telefono" id="edit_c_telefono" class="form-input">
            </div>
            
            <div class="form-group" style="margin-bottom: 24px;">
                <label for="edit_c_direccion" class="form-label">Dirección Domiciliaria</label>
                <input type="text" name="direccion" id="edit_c_direccion" class="form-input">
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn-secondary" onclick="closeEditClientModal()">Cancelar</button>
                <button type="submit" class="btn-primary" style="width: auto; background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 4px 16px rgba(16, 185, 129, 0.25);">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDICIÓN DE USUARIO -->
<div id="edit-user-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; justify-content: center; align-items: center; padding: 20px; backdrop-filter: blur(5px);">
    <div class="glass-panel" style="width: 100%; max-width: 450px; padding: 30px; background: #0f172a; border-color: rgba(59, 130, 246, 0.2);">
        <h3 style="font-family: var(--font-heading); font-size: 1.25rem; color: #3b82f6; margin-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 8px;">
            Editar Cuenta de Acceso
        </h3>
        
        <form id="edit-user-form" action="" method="POST">
            <div class="form-group">
                <label for="edit_u_username" class="form-label">Nombre de Usuario</label>
                <input type="text" name="username" id="edit_u_username" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="edit_u_nombre" class="form-label">Nombre Completo</label>
                <input type="text" name="nombre_completo" id="edit_u_nombre" class="form-input" required>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 12px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_u_ci" class="form-label">CI / NIT</label>
                    <input type="text" name="ci_nit" id="edit_u_ci" class="form-input" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_u_telefono" class="form-label">Teléfono</label>
                    <input type="text" name="telefono" id="edit_u_telefono" class="form-input">
                </div>
            </div>
            
            <div class="form-group">
                <label for="edit_u_direccion" class="form-label">Dirección</label>
                <input type="text" name="direccion" id="edit_u_direccion" class="form-input">
            </div>
            
            <div class="form-group">
                <label for="edit_u_rol" class="form-label">Rol del Usuario</label>
                <select name="id_rol" id="edit_u_rol" class="select-input">
                    <option value="1">Administrador</option>
                    <option value="2">Cliente</option>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 24px;">
                <label for="edit_u_password" class="form-label">Contraseña (Dejar en blanco para no cambiar)</label>
                <input type="password" name="password" id="edit_u_password" class="form-input" placeholder="Nueva contraseña (opcional)">
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn-secondary" onclick="closeEditUserModal()">Cancelar</button>
                <button type="submit" class="btn-primary" style="width: auto; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>


<script>
    function switchAdminTab(tab) {
        document.getElementById('nav-usuarios').classList.remove('active');
        document.getElementById('nav-clientes').classList.remove('active');
        document.getElementById('nav-inventario').classList.remove('active');
        const navCrear = document.getElementById('nav-crear-cliente');
        if (navCrear) navCrear.classList.remove('active');
        document.getElementById('nav-' + tab).classList.add('active');
        
        const panels = document.getElementsByClassName('admin-panel-content');
        Array.from(panels).forEach(panel => {
            panel.classList.remove('active');
        });
        document.getElementById('tab-' + tab + '-content').classList.add('active');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const activeTabParam = urlParams.get('active_tab');
        if (activeTabParam) {
            switchAdminTab(activeTabParam);
        }
    });

    // Control Modales
    function openEditProductModal(id, nombre, codigo, precio, stock, stockMin, catId) {
        const modal = document.getElementById('edit-product-modal');
        const form = document.getElementById('edit-product-form');
        
        // Cargar valores
        document.getElementById('edit_p_nombre').value = nombre;
        document.getElementById('edit_p_codigo').value = codigo;
        document.getElementById('edit_p_precio').value = precio;
        document.getElementById('edit_p_stock').value = stock;
        document.getElementById('edit_p_stock_min').value = stockMin;
        document.getElementById('edit_p_cat').value = catId;
        
        // Poner url correcta
        form.action = `/admin/inventory/edit/${id}`;
        
        modal.style.display = 'flex';
    }

    function closeEditProductModal() {
        document.getElementById('edit-product-modal').style.display = 'none';
    }

    function openEditClientModal(id, nombre, ci, telefono, direccion) {
        const modal = document.getElementById('edit-client-modal');
        const form = document.getElementById('edit-client-form');
        
        // Cargar valores
        document.getElementById('edit_c_nombre').value = nombre;
        document.getElementById('edit_c_ci').value = ci;
        document.getElementById('edit_c_telefono').value = telefono;
        document.getElementById('edit_c_direccion').value = direccion;
        
        // Poner url
        form.action = `/admin/clients/edit/${id}`;
        
        modal.style.display = 'flex';
    }

    function closeEditClientModal() {
        document.getElementById('edit-client-modal').style.display = 'none';
    }

    function openEditUserModal(id, username, nombre, telefono, direccion, rolId, ci) {
        const modal = document.getElementById('edit-user-modal');
        const form = document.getElementById('edit-user-form');
        
        // Cargar valores
        document.getElementById('edit_u_username').value = username;
        document.getElementById('edit_u_nombre').value = nombre;
        document.getElementById('edit_u_telefono').value = telefono;
        document.getElementById('edit_u_direccion').value = direccion;
        document.getElementById('edit_u_rol').value = rolId;
        document.getElementById('edit_u_ci').value = ci;
        document.getElementById('edit_u_password').value = ''; // Clean password field
        
        // Poner url
        form.action = `/admin/users/edit/${id}`;
        
        modal.style.display = 'flex';
    }

    function closeEditUserModal() {
        document.getElementById('edit-user-modal').style.display = 'none';
    }
</script>
{% endblock %}
