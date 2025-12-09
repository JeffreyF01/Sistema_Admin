/**
 * ========================================
 * SISTEMA DE VALIDACIONES
 * Validación de campos en tiempo real
 * ========================================
 */

const Validaciones = {
    
    /**
     * Solo letras y espacios (nombres, apellidos, ciudades)
     */
    soloLetras: function(selector) {
        $(selector).on('input', function(){
            this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '');
        });
    },
    
    /**
     * Solo números (teléfonos, RNC, cédulas)
     */
    soloNumeros: function(selector) {
        $(selector).on('input', function(){
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    },
    
    /**
     * Alfanumérico con espacios (direcciones, referencias)
     */
    alfanumerico: function(selector) {
        $(selector).on('input', function(){
            this.value = this.value.replace(/[^A-Za-z0-9ÁÉÍÓÚáéíóúÑñ\s,.\-#°]/g, '');
        });
    },
    
    /**
     * Código/SKU (letras, números, guión, guión bajo) - UPPERCASE
     */
    codigo: function(selector) {
        $(selector).on('input', function(){
            this.value = this.value.replace(/[^A-Za-z0-9\-_]/g, '').toUpperCase();
        });
    },
    
    /**
     * Decimal positivo (precios, costos, cantidades)
     * @param {string} selector - Selector jQuery
     * @param {number} decimales - Número de decimales permitidos (default: 2)
     */
    decimal: function(selector, decimales = 2) {
        $(selector).on('input', function(){
            let val = this.value.replace(/[^0-9.]/g, '');
            
            // Solo un punto decimal
            const parts = val.split('.');
            if (parts.length > 2) {
                val = parts[0] + '.' + parts.slice(1).join('');
            }
            
            // Limitar decimales
            if (parts[1] && parts[1].length > decimales) {
                val = parts[0] + '.' + parts[1].substring(0, decimales);
            }
            
            this.value = val;
        });
    },
    
    /**
     * Limitar longitud máxima de caracteres
     */
    maxLength: function(selector, max) {
        $(selector).on('input', function(){
            if(this.value.length > max){
                this.value = this.value.substring(0, max);
            }
        });
    },
    
    /**
     * Email válido
     */
    validarEmail: function(email) {
        if (!email || email.trim() === '') return true; // Permitir vacío si no es required
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim());
    },
    
    /**
     * Teléfono válido (números, espacios, guiones, paréntesis, +)
     */
    validarTelefono: function(tel) {
        if (!tel || tel.trim() === '') return true;
        return /^[0-9\-\+\(\)\s]+$/.test(tel);
    },
    
    /**
     * RNC/Cédula (solo números, longitud variable)
     */
    validarRNC: function(rnc) {
        if (!rnc || rnc.trim() === '') return true;
        return /^[0-9]{9,11}$/.test(rnc.replace(/\-/g, ''));
    },
    
    /**
     * Validar URL
     */
    validarURL: function(url) {
        if (!url || url.trim() === '') return true;
        return /^https?:\/\/.+/.test(url);
    },
    
    /**
     * Validar que un número sea positivo
     */
    validarPositivo: function(valor) {
        const num = parseFloat(valor);
        return !isNaN(num) && num > 0;
    },
    
    /**
     * Validar que un número sea no negativo (>= 0)
     */
    validarNoNegativo: function(valor) {
        const num = parseFloat(valor);
        return !isNaN(num) && num >= 0;
    },
    
    /**
     * Aplicar validaciones automáticas por tipo de campo
     */
    autoAplicar: function() {
        // Campos de nombre
        this.soloLetras('input[name="nombre"], input[name*="nombre"], input[id*="nombre"]');
        this.soloLetras('input[name="apellido"], input[name*="apellido"]');
        this.soloLetras('input[name="ciudad"], input[name*="ciudad"]');
        
        // Campos numéricos
        this.soloNumeros('input[name="telefono"], input[name*="telefono"], input[id*="telefono"]');
        this.soloNumeros('input[name="rnc"], input[name*="rnc"], input[id*="rnc"]');
        this.soloNumeros('input[name="cedula"], input[name*="cedula"]');
        this.soloNumeros('input[name*="doc_identidad"]');
        
        // Códigos/SKU
        this.codigo('input[name="sku"], input[name*="sku"], input[id*="sku"]');
        this.codigo('input[name="codigo"], input[name*="codigo"], input[id*="codigo"]');
        
        // Direcciones
        this.alfanumerico('input[name="direccion"], input[name*="direccion"], textarea[name="direccion"]');
        this.alfanumerico('input[name="referencia"], textarea[name="referencia"]');
        this.alfanumerico('textarea[name="descripcion"]');
        
        // Decimales
        this.decimal('input[name*="precio"], input[id*="precio"]', 2);
        this.decimal('input[name*="costo"], input[id*="costo"]', 4);
        this.decimal('input[name*="cantidad"], input[id*="cantidad"]', 4);
        this.decimal('input[name*="monto"], input[id*="monto"]', 2);
        this.decimal('input[name*="tasa"], input[id*="tasa"]', 6);
        this.decimal('input[name*="stock"]', 4);
    },
    
    /**
     * Mostrar error en campo específico
     */
    mostrarError: function(campo, mensaje) {
        Swal.fire({
            icon: 'error',
            title: 'Error de validación',
            text: mensaje,
            confirmButtonColor: '#dc3545'
        });
        $(campo).focus();
    },
    
    /**
     * Validar formulario completo antes de enviar
     */
    validarFormulario: function(formSelector) {
        const form = $(formSelector);
        let valido = true;
        let primerError = null;
        
        // Validar campos required
        form.find('input[required], select[required], textarea[required]').each(function(){
            if (!$(this).val() || $(this).val().trim() === '') {
                valido = false;
                if (!primerError) {
                    primerError = this;
                    const label = form.find('label[for="' + this.id + '"]').text() || 'Este campo';
                    Validaciones.mostrarError(this, label + ' es obligatorio');
                }
                return false; // break
            }
        });
        
        if (!valido) return false;
        
        // Validar emails
        form.find('input[type="email"]').each(function(){
            const val = $(this).val();
            if (val && !Validaciones.validarEmail(val)) {
                valido = false;
                if (!primerError) {
                    primerError = this;
                    Validaciones.mostrarError(this, 'Email inválido');
                }
                return false;
            }
        });
        
        if (!valido) return false;
        
        // Validar números positivos
        form.find('input[name*="precio"], input[name*="costo"]').each(function(){
            if ($(this).val() && !Validaciones.validarPositivo($(this).val())) {
                valido = false;
                if (!primerError) {
                    primerError = this;
                    const label = form.find('label[for="' + this.id + '"]').text() || 'Este campo';
                    Validaciones.mostrarError(this, label + ' debe ser mayor a 0');
                }
                return false;
            }
        });
        
        return valido;
    }
};

// Auto-aplicar validaciones cuando el DOM esté listo
$(document).ready(function() {
    Validaciones.autoAplicar();
    
    // Prevenir envío de formularios con Enter (excepto textareas)
    $('form input').not('textarea').on('keypress', function(e) {
        if (e.which === 13 && $(this).attr('type') !== 'submit') {
            e.preventDefault();
            return false;
        }
    });
});
