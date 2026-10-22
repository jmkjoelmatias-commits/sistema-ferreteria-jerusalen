"""
Pruebas de interfaz con Selenium WebDriver
Requisito: pip install selenium  +  ChromeDriver en el PATH
Descarga ChromeDriver (misma versión que tu Chrome):
  https://chromedriver.chromium.org/downloads
Corre con: pytest tests\selenium\test_login_ui.py -v
"""
import pytest
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options

BASE_URL = "http://localhost"


@pytest.fixture(scope="module")
def driver():
    """Abre Chrome una sola vez para todos los tests del archivo."""
    opciones = Options()
    # opciones.add_argument("--headless")  # Descomenta si no quieres ver la ventana
    opciones.add_argument("--no-sandbox")
    opciones.add_argument("--disable-dev-shm-usage")
    navegador = webdriver.Chrome(options=opciones)
    navegador.implicitly_wait(5)
    yield navegador
    navegador.quit()


class TestLoginUI:

    def test_pagina_login_carga(self, driver):
        """La página de login debe cargar y mostrar el formulario"""
        driver.get(f"{BASE_URL}/index.php")
        assert driver.find_element(By.TAG_NAME, "form"), \
            "Debe existir un formulario de login en la página"

    def test_login_exitoso_redirige_dashboard(self, driver):
        """CP-01: Credenciales válidas → redirige al dashboard"""
        driver.get(f"{BASE_URL}/index.php")
        driver.find_element(By.NAME, "username").clear()
        driver.find_element(By.NAME, "username").send_keys("admin")
        driver.find_element(By.NAME, "password").clear()
        driver.find_element(By.NAME, "password").send_keys("admin123")
        driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()

        WebDriverWait(driver, 5).until(EC.url_contains("dashboard"))
        assert "dashboard" in driver.current_url.lower(), \
            "Tras login exitoso debe redirigir al dashboard"

    def test_login_fallido_muestra_error(self, driver):
        """CP-02: Contraseña incorrecta → mensaje de error visible en pantalla"""
        driver.get(f"{BASE_URL}/index.php")
        driver.find_element(By.NAME, "username").clear()
        driver.find_element(By.NAME, "username").send_keys("admin")
        driver.find_element(By.NAME, "password").clear()
        driver.find_element(By.NAME, "password").send_keys("CLAVE_INCORRECTA")
        driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()

        # ⚠️ Ajusta el selector al class o id real de tu mensaje de error en el HTML
        error = WebDriverWait(driver, 5).until(
            EC.visibility_of_element_located((By.CLASS_NAME, "error-message"))
        )
        assert error.is_displayed(), \
            "Debería mostrarse un mensaje de error al fallar el login"

    def test_agregar_producto_al_carrito(self, driver):
        """CP-09: Click en Añadir → producto aparece en la lista del carrito"""
        driver.get(f"{BASE_URL}/public/views/cotizaciones.php")

        # ⚠️ Ajusta el selector al botón real de tu interfaz
        boton = WebDriverWait(driver, 5).until(
            EC.element_to_be_clickable((By.CSS_SELECTOR, ".btn-agregar-carrito"))
        )
        boton.click()

        carrito = driver.find_element(By.ID, "lista-carrito")
        items   = carrito.find_elements(By.TAG_NAME, "li")
        assert len(items) > 0, \
            "El carrito debería tener al menos un producto tras hacer click en Añadir"