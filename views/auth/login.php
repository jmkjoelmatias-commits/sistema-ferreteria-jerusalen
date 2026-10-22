{% extends "layout/base.php" %}

{% block title %}Iniciar Sesión{% endblock %}

{% block content %}
<div class="login-container">
    <div class="glass-panel login-card">
        
        <!-- Avatar del Robot Asistente -->
        <div class="robot-avatar-wrapper">
            <img src="{{ url_for('static', filename='img/robot_avatar.png') }}" alt="Robot Jerusalén" class="robot-avatar" id="robot-img">
        </div>
        
        <h2 style="text-align: center; margin-bottom: 8px; font-family: var(--font-heading); font-size: 1.6rem;">Ferretería Jerusalén</h2>
        <p style="text-align: center; color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 24px;">Asistencia Técnica e Ingeniería en Obras</p>

        <!-- Selector de Rol (Tabs) -->
        <div class="role-tabs">
            <button type="button" class="role-tab-btn {% if active_tab == 'Cliente' or not active_tab %}active{% endif %}" onclick="selectRole('Cliente')">Cliente</button>
            <button type="button" class="role-tab-btn {% if active_tab == 'Administrador' %}active{% endif %}" onclick="selectRole('Administrador')">Administrador</button>
        </div>

        <!-- Formulario de Login -->
        <form action="{{ url_for('auth.login') }}" method="POST" id="login-form">
            <!-- Rol Activo Oculto -->
            <input type="hidden" name="rol" id="selected-rol" value="{% if active_tab %}{{ active_tab }}{% else %}Cliente{% endif %}">
            
            <div class="form-group">
                <label for="username" class="form-label">Nombre de Usuario</label>
                <input type="text" name="username" id="username" class="form-input" placeholder="ej. joel_admin" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" name="password" id="password" class="form-input" placeholder="••••••••" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn-primary">
                <span>Ingresar al Sistema</span>
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l-4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
            </button>
        </form>

    </div>
</div>

<script>
    function selectRole(role) {
        document.getElementById('selected-rol').value = role;
        
        const buttons = document.getElementsByClassName('role-tab-btn');
        Array.from(buttons).forEach(btn => {
            if (btn.innerText === role) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        const robotImg = document.getElementById('robot-img');
        if (role === 'Administrador') {
            robotImg.style.animation = 'float 2s ease-in-out infinite';
        } else {
            robotImg.style.animation = 'float 4s ease-in-out infinite';
        }
    }
</script>
{% endblock %}
