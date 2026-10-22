<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{% block title %}Ferretería Jerusalén{% endblock %} - Gestión e Ingeniería</title>
    <meta name="description" content="Sistema de cotización inteligente, control de inventarios y asistencia técnica en construcción para la Ferretería Jerusalén.">
    <!-- Hojas de estilo y fuentes -->
    <link rel="stylesheet" href="{{ url_for('static', filename='css/estilos.css') }}">
    {% block extra_head %}{% endblock %}
</head>
<body>

    <!-- Mensajes Flash flotantes -->
    <div class="flash-messages" id="flash-container">
        {% with messages = get_flashed_messages(with_categories=true) %}
            {% if messages %}
                {% for category, message in messages %}
                    <div class="flash-message {{ category }}">
                        {% if category == 'success' %}
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {% elif category == 'error' %}
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {% else %}
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {% endif %}
                        <span>{{ message }}</span>
                    </div>
                {% endfor %}
            {% endif %}
        {% endwith %}
    </div>

    <!-- Contenido Principal -->
    {% block body_layout %}
        {% block content %}{% endblock %}
    {% endblock %}

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('flash-container');
            if (container) {
                const messages = container.getElementsByClassName('flash-message');
                Array.from(messages).forEach(msg => {
                    setTimeout(() => {
                        msg.style.opacity = '0';
                        msg.style.transform = 'translateY(-20px)';
                        msg.style.transition = 'all 0.4s ease';
                        setTimeout(() => msg.remove(), 400);
                    }, 5000);
                });
            }
        });
    </script>
</body>
</html>
