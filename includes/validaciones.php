<?php
/**
 * ========================================
 * FUNCIONES DE VALIDACIÓN BACKEND
 * Para usar en archivos *_ajax.php
 * ========================================
 */

class Validador {
    
    /**
     * Validar que un campo solo contenga letras y espacios
     */
    public static function soloLetras($valor, $campo = 'Campo') {
        if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $valor)) {
            return ['valido' => false, 'mensaje' => "$campo solo debe contener letras y espacios"];
        }
        return ['valido' => true];
    }
    
    /**
     * Validar que un campo solo contenga números
     */
    public static function soloNumeros($valor, $campo = 'Campo') {
        if (!preg_match('/^[0-9]+$/', $valor)) {
            return ['valido' => false, 'mensaje' => "$campo solo debe contener números"];
        }
        return ['valido' => true];
    }
    
    /**
     * Validar código/SKU (alfanumérico + guiones)
     */
    public static function validarCodigo($valor, $campo = 'Código') {
        if (!preg_match('/^[A-Za-z0-9\-_]+$/', $valor)) {
            return ['valido' => false, 'mensaje' => "$campo solo debe contener letras, números, guiones y guión bajo"];
        }
        return ['valido' => true];
    }
    
    /**
     * Validar email
     */
    public static function validarEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['valido' => false, 'mensaje' => 'Email inválido'];
        }
        return ['valido' => true];
    }
    
    /**
     * Validar teléfono (números, espacios, guiones, paréntesis, +)
     */
    public static function validarTelefono($telefono, $campo = 'Teléfono') {
        if (!preg_match('/^[0-9\-\+\(\)\s]+$/', $telefono)) {
            return ['valido' => false, 'mensaje' => "$campo solo debe contener números, espacios, guiones, paréntesis y el signo +"];
        }
        return ['valido' => true];
    }
    
    /**
     * Validar RNC/Cédula
     */
    public static function validarRNC($rnc, $campo = 'RNC') {
        // Remover guiones para validar
        $rnc_limpio = str_replace('-', '', $rnc);
        if (!preg_match('/^[0-9]{9,11}$/', $rnc_limpio)) {
            return ['valido' => false, 'mensaje' => "$campo debe tener entre 9 y 11 dígitos"];
        }
        return ['valido' => true];
    }
    
    /**
     * Validar número positivo
     */
    public static function validarPositivo($valor, $campo = 'Valor') {
        $num = floatval($valor);
        if ($num <= 0) {
            return ['valido' => false, 'mensaje' => "$campo debe ser mayor a 0"];
        }
        return ['valido' => true];
    }
    
    /**
     * Validar número no negativo (>= 0)
     */
    public static function validarNoNegativo($valor, $campo = 'Valor') {
        $num = floatval($valor);
        if ($num < 0) {
            return ['valido' => false, 'mensaje' => "$campo no puede ser negativo"];
        }
        return ['valido' => true];
    }
    
    /**
     * Validar longitud de cadena
     */
    public static function validarLongitud($valor, $min, $max, $campo = 'Campo') {
        $len = strlen($valor);
        if ($len < $min || $len > $max) {
            return ['valido' => false, 'mensaje' => "$campo debe tener entre $min y $max caracteres"];
        }
        return ['valido' => true];
    }
    
    /**
     * Validar que un campo no esté vacío
     */
    public static function noVacio($valor, $campo = 'Campo') {
        if (empty(trim($valor))) {
            return ['valido' => false, 'mensaje' => "$campo es obligatorio"];
        }
        return ['valido' => true];
    }
    
    /**
     * Validar URL
     */
    public static function validarURL($url) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return ['valido' => false, 'mensaje' => 'URL inválida'];
        }
        return ['valido' => true];
    }
    
    /**
     * Sanitizar string (remover tags HTML y caracteres especiales)
     */
    public static function sanitizar($valor) {
        return htmlspecialchars(strip_tags(trim($valor)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validar y sanitizar múltiples campos
     * 
     * Ejemplo de uso:
     * $campos = [
     *     ['valor' => $_POST['nombre'], 'tipo' => 'letras', 'campo' => 'Nombre'],
     *     ['valor' => $_POST['email'], 'tipo' => 'email', 'campo' => 'Email'],
     *     ['valor' => $_POST['precio'], 'tipo' => 'positivo', 'campo' => 'Precio']
     * ];
     * 
     * $resultado = Validador::validarMultiple($campos);
     * if (!$resultado['valido']) {
     *     echo json_encode(['success' => false, 'message' => $resultado['mensaje']]);
     *     exit;
     * }
     */
    public static function validarMultiple($campos) {
        foreach ($campos as $campo) {
            $valor = $campo['valor'] ?? '';
            $tipo = $campo['tipo'] ?? '';
            $nombre = $campo['campo'] ?? 'Campo';
            $obligatorio = $campo['obligatorio'] ?? true;
            
            // Si es opcional y está vacío, continuar
            if (!$obligatorio && empty(trim($valor))) {
                continue;
            }
            
            // Validar no vacío si es obligatorio
            if ($obligatorio) {
                $validacion = self::noVacio($valor, $nombre);
                if (!$validacion['valido']) {
                    return $validacion;
                }
            }
            
            // Validar según tipo
            switch ($tipo) {
                case 'letras':
                    $validacion = self::soloLetras($valor, $nombre);
                    break;
                case 'numeros':
                    $validacion = self::soloNumeros($valor, $nombre);
                    break;
                case 'codigo':
                    $validacion = self::validarCodigo($valor, $nombre);
                    break;
                case 'email':
                    $validacion = self::validarEmail($valor);
                    break;
                case 'telefono':
                    $validacion = self::validarTelefono($valor, $nombre);
                    break;
                case 'rnc':
                    $validacion = self::validarRNC($valor, $nombre);
                    break;
                case 'positivo':
                    $validacion = self::validarPositivo($valor, $nombre);
                    break;
                case 'no_negativo':
                    $validacion = self::validarNoNegativo($valor, $nombre);
                    break;
                default:
                    // Si no se especifica tipo, solo sanitizar
                    continue 2;
            }
            
            if (!$validacion['valido']) {
                return $validacion;
            }
        }
        
        return ['valido' => true];
    }
}
?>
