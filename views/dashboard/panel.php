{% extends "layout/base.php" %}

{% block title %}Panel del Cliente{% endblock %}

{% block body_layout %}
<div class="dashboard-layout">
    
    <!-- Barra Lateral (Sidebar) -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">J</div>
            <div class="brand-name">Jerusalén IA</div>
        </div>
        
        <div class="sidebar-nav">
            <a href="javascript:void(0)" class="nav-link active" id="nav-catalogo" onclick="switchTab('catalogo')">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <span>Catálogo & Compra</span>
            </a>
            <a href="javascript:void(0)" class="nav-link" id="nav-consultor" onclick="switchTab('consultor')">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                <span>Asistente Consultor IA</span>
            </a>
            <a href="{{ url_for('auth.logout') }}" class="nav-link" style="margin-top: 20px; color: var(--error);">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span>Cerrar Sesión</span>
            </a>
        </div>
        
        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="user-avatar">
                    {{ session.get('username')[0].upper() }}
                </div>
                <div class="user-info">
                    <span class="user-name">{{ cliente_info[0] }}</span>
                    <span class="user-role">Cliente Registrado</span>
                </div>
            </div>
            <div style="font-size: 0.75rem; color: var(--text-secondary); text-align: center;">
                CI: {{ cliente_info[1] }}
            </div>
        </div>
    </div>
    
    <!-- Contenido Principal -->
    <div class="main-content">
        
        <!-- ================= OPCIÓN A: CATÁLOGO Y CARRITO ================= -->
        <div id="tab-catalogo-content">
            <div class="dashboard-grid">
                
                <!-- Catálogo de Productos -->
                <div>
                    <h2 class="section-title">Catálogo de Materiales</h2>
                    <p class="section-desc">Seleccione los insumos de construcción que requiere para su cotización tradicional.</p>
                    
                    <!-- Buscador -->
                    <div class="search-box">
                        <input type="text" id="search-input" class="search-input" placeholder="Buscar materiales por nombre o categoría..." onkeyup="filterProducts()">
                    </div>
                    
                    <div class="product-grid" id="product-list">
                        {% for prod in productos %}
                        <div class="glass-panel product-card" data-name="{{ prod[2].lower() }}" data-cat="{{ prod[6].lower() }}">
                            <span class="product-category">{{ prod[6] }}</span>
                            <h3 class="product-name">{{ prod[2] }}</h3>
                            
                            <div class="product-meta">
                                <div class="product-price">
                                    <span class="price-label">Precio Unitario</span>
                                    <span class="price-value">{{ prod[3] }} Bs.</span>
                                </div>
                                <div class="product-stock">
                                    <span class="stock-label">Disponible</span>
                                    <span class="stock-value {% if prod[4] <= prod[5] %}low{% endif %}" id="stock-val-{{ prod[0] }}">{{ prod[4] }} u.</span>
                                </div>
                            </div>
                            
                            {% if prod[4] > 0 %}
                            <button type="button" class="btn-primary" onclick="addToCart('{{ prod[0] }}', '{{ prod[2] }}', '{{ prod[3] }}', {{ prod[4] }})">
                                <span>Añadir al Carrito</span>
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </button>
                            {% else %}
                            <button type="button" class="btn-secondary" style="width: 100%; cursor: not-allowed;" disabled>
                                <span style="color: var(--error);">Sin Stock</span>
                            </button>
                            {% endif %}
                        </div>
                        {% endfor %}
                    </div>
                </div>
                
                <!-- Carrito de Compras Lateral -->
                <div class="glass-panel cart-panel">
                    <h3 class="cart-title">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <span>Mi Cotización</span>
                    </h3>
                    
                    <div class="cart-items" id="cart-items-container">
                        <div style="text-align: center; color: var(--text-secondary); margin-top: 40px; font-size: 0.9rem;">
                            El carrito está vacío. Añada productos desde el catálogo.
                        </div>
                    </div>
                    
                    <div class="cart-summary">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span id="cart-subtotal">0.00 Bs.</span>
                        </div>
                        <div class="summary-row">
                            <span>Impuestos (Ley)</span>
                            <span>Incluidos</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total Cotización</span>
                            <span id="cart-total" style="color: var(--accent-amber);">0.00 Bs.</span>
                        </div>
                    </div>
                    
                    <form action="{{ url_for('client.generate_quote') }}" method="POST" id="checkout-form">
                        <input type="hidden" name="cart_data" id="cart-data-input">
                        <button type="submit" class="btn-primary" id="checkout-btn" disabled>
                            <span>Generar Proforma PDF</span>
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </button>
                    </form>
                </div>
                
            </div>
        </div>
        
        <!-- ================= OPCIÓN B: ASISTENTE CONSULTOR IA ================= -->
        <div id="tab-consultor-content" style="display: none;">
            <div style="max-width: 800px; margin: 0 auto;">
                <h2 class="section-title">Asistente Consultor de Construcción</h2>
                <p class="section-desc">Simulador técnico inteligente que calcula cantidades de materiales estimadas según las dimensiones y condiciones físicas de su obra.</p>
                
                <div class="glass-panel" style="padding: 40px; text-align: center; border-color: rgba(245, 158, 11, 0.2);">
                    <div style="font-size: 3rem; margin-bottom: 20px; animation: pulse 2s infinite;">🚧</div>
                    <h3 style="font-family: var(--font-heading); font-size: 1.5rem; color: var(--accent-amber); margin-bottom: 12px;">Módulo en Desarrollo</h3>
                    <p style="color: var(--text-secondary); max-width: 500px; margin: 0 auto 24px auto; line-height: 1.6;">
                        El Asistente Consultor de Obras con Inteligencia Artificial está temporalmente en pausa. Estamos trabajando paso a paso para ofrecerle un motor de estimación técnica de alta precisión.
                    </p>
                    <button class="btn-secondary" style="width: auto; margin: 0 auto; cursor: not-allowed; opacity: 0.6;" disabled>
                        <span>Próximamente Disponible</span>
                    </button>
                </div>
            </div>
        </div>
        
    </div>
</div>

<script>
    function switchTab(tab) {
        document.getElementById('nav-catalogo').classList.remove('active');
        document.getElementById('nav-consultor').classList.remove('active');
        document.getElementById('nav-' + tab).classList.add('active');
        
        document.getElementById('tab-catalogo-content').style.display = tab === 'catalogo' ? 'block' : 'none';
        document.getElementById('tab-consultor-content').style.display = tab === 'consultor' ? 'block' : 'none';
    }

    function filterProducts() {
        const query = document.getElementById('search-input').value.toLowerCase().trim();
        const cards = document.getElementById('product-list').getElementsByClassName('product-card');
        
        Array.from(cards).forEach(card => {
            const name = card.getAttribute('data-name');
            const cat = card.getAttribute('data-cat');
            if (name.includes(query) || cat.includes(query)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    let cart = [];

    function addToCart(id, name, price, maxStock) {
        id = parseInt(id);
        price = parseFloat(price);
        
        const existingItem = cart.find(item => item.id === id);
        if (existingItem) {
            if (existingItem.quantity < maxStock) {
                existingItem.quantity++;
            } else {
                alert(`Límite de stock alcanzado para '${name}'. Solo hay ${maxStock} unidades disponibles.`);
            }
        } else {
            cart.push({
                id: id,
                name: name,
                price: price,
                quantity: 1,
                maxStock: maxStock
            });
        }
        updateCartUI();
    }

    function changeQty(id, delta) {
        id = parseInt(id);
        const item = cart.find(i => i.id === id);
        if (item) {
            item.quantity += delta;
            if (item.quantity <= 0) {
                cart = cart.filter(i => i.id !== id);
            } else if (item.quantity > item.maxStock) {
                alert(`Stock máximo disponible alcanzado (${item.maxStock} u.)`);
                item.quantity = item.maxStock;
            }
        }
        updateCartUI();
    }

    function updateCartUI() {
        const container = document.getElementById('cart-items-container');
        const checkoutBtn = document.getElementById('checkout-btn');
        const cartDataInput = document.getElementById('cart-data-input');
        
        if (cart.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; color: var(--text-secondary); margin-top: 40px; font-size: 0.9rem;">
                    El carrito está vacío. Añada productos desde el catálogo.
                </div>
            `;
            checkoutBtn.disabled = true;
            cartDataInput.value = '';
            
            document.getElementById('cart-subtotal').innerText = '0.00 Bs.';
            document.getElementById('cart-total').innerText = '0.00 Bs.';
            return;
        }
        
        checkoutBtn.disabled = false;
        let subtotal = 0.0;
        
        container.innerHTML = '';
        cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            
            const div = document.createElement('div');
            div.className = 'cart-item';
            div.innerHTML = `
                <div class="cart-item-info">
                    <span class="cart-item-name" title="${item.name}">${item.name}</span>
                    <span class="cart-item-price">${item.price.toFixed(2)} Bs.</span>
                </div>
                <div class="cart-item-actions">
                    <button type="button" class="qty-btn" onclick="changeQty(${item.id}, -1)">-</button>
                    <span class="cart-item-qty">${item.quantity}</span>
                    <button type="button" class="qty-btn" onclick="changeQty(${item.id}, 1)">+</button>
                </div>
            `;
            container.appendChild(div);
        });
        
        document.getElementById('cart-subtotal').innerText = subtotal.toFixed(2) + ' Bs.';
        document.getElementById('cart-total').innerText = subtotal.toFixed(2) + ' Bs.';
        
        cartDataInput.value = JSON.stringify(cart);
    }
</script>
{% endblock %}
