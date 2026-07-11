<?php
/**
 * Compilador de documentación — Genera un archivo DOCX unificado a partir de
 * los manuales de Usuario y Sistema escritos en Markdown.
 * 
 * Utiliza la librería PhpOffice\PhpWord para crear el documento Word2007
 * con estilos, portada, alertas, bloques de código y marcadores de captura de pantalla.
 */

// Cargar el autoloader de Composer (PhpWord disponible vía Composer)
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Shared\Converter;

/**
 * Función principal: compila dos archivos Markdown en un único documento DOCX.
 * 
 * @param string $userManualPath   Ruta al archivo Markdown del manual de usuario.
 * @param @param string $systemManualPath Ruta al archivo Markdown del manual de sistema.
 * @param string $outputPath       Ruta de salida del archivo DOCX generado.
 */
function compileMarkdownToDocx($userManualPath, $systemManualPath, $outputPath) {
    // Validar que los archivos fuente existan antes de continuar
    if (!file_exists($userManualPath) || !file_exists($systemManualPath)) {
        echo "Error: Source markdown files not found.\n";
        return;
    }

    echo "Starting DOCX generation...\n";

    // Crear una nueva instancia del documento Word
    $phpWord = new PhpWord();

    // Configurar fuentes y estilos de títulos
    $phpWord->addTitleStyle(1, ['name' => 'Poppins', 'size' => 20, 'color' => '1F4E79', 'bold' => true], ['spaceAfter' => 240, 'spaceBefore' => 360]);
    $phpWord->addTitleStyle(2, ['name' => 'Poppins', 'size' => 14, 'color' => '2E75B6', 'bold' => true], ['spaceAfter' => 180, 'spaceBefore' => 240]);
    $phpWord->addTitleStyle(3, ['name' => 'Poppins', 'size' => 12, 'color' => '418AB3', 'bold' => true], ['spaceAfter' => 120, 'spaceBefore' => 180]);
    $phpWord->addTitleStyle(4, ['name' => 'Poppins', 'size' => 11, 'color' => '5B9BD5', 'bold' => true], ['spaceAfter' => 60, 'spaceBefore' => 120]);

    // Establecer márgenes y sección principal
    $section = $phpWord->addSection([
        'marginLeft' => Converter::cmToTwip(2.5),
        'marginRight' => Converter::cmToTwip(2.5),
        'marginTop' => Converter::cmToTwip(2.5),
        'marginBottom' => Converter::cmToTwip(2.5),
    ]);

    // --- PORTADA ---
    $section->addText("SISTEMA DE GESTIÓN OPERATIVA", ['name' => 'Poppins', 'size' => 14, 'color' => '5B9BD5', 'bold' => true], ['alignment' => Jc::CENTER, 'spaceAfter' => 240]);
    $section->addText("BETEL CREATIVA", ['name' => 'Poppins', 'size' => 32, 'color' => '1F4E79', 'bold' => true], ['alignment' => Jc::CENTER, 'spaceAfter' => 120, 'spaceBefore' => 240]);
    $section->addText("Manual de Usuario y Manual de Sistema", ['name' => 'Poppins', 'size' => 16, 'color' => '595959', 'italic' => true], ['alignment' => Jc::CENTER, 'spaceAfter' => 1200]);
    
    $section->addText("Desarrollado para la gestión de eventos, inventario, clientes y facturación.", ['name' => 'Calibri', 'size' => 11, 'color' => '595959'], ['alignment' => Jc::CENTER, 'spaceAfter' => 2400]);
    
    // Fecha y versión del documento generado
    $section->addText("Fecha: " . date('Y-m-d'), ['name' => 'Calibri', 'size' => 11, 'bold' => true], ['alignment' => Jc::CENTER]);
    $section->addText("Versión: 1.0", ['name' => 'Calibri', 'size' => 11], ['alignment' => Jc::CENTER]);
    $section->addPageBreak();

    // --- PARTE I: MANUAL DE USUARIO ---
    $section->addTitle("PARTE I: MANUAL DE USUARIO", 1);
    parseMarkdownFile($section, $userManualPath);

    // Salto de página entre las dos partes
    $section->addPageBreak();

    // --- PARTE II: MANUAL DE SISTEMA ---
    $section->addTitle("PARTE II: MANUAL DE SISTEMA", 1);
    parseMarkdownFile($section, $systemManualPath);

    // --- Guardar el documento en formato Word2007 (.docx) ---
    echo "Saving document to $outputPath...\n";
    $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
    $objWriter->save($outputPath);
    echo "Document saved successfully!\n";
}

/**
 * Parsea un archivo Markdown línea por línea y añade su contenido a la sección DOCX.
 * 
 * Soporta: encabezados (# a ####), bloques de código, alertas (> [!NOTE]),
 * marcadores de captura de pantalla, listas y párrafos con formato inline (**negrita**).
 *
 * @param object $section  Objeto de sección de PhpWord.
 * @param string $filePath Ruta completa al archivo Markdown a procesar.
 */
function parseMarkdownFile($section, $filePath) {
    // Leer todas las líneas del archivo Markdown
    $lines = file($filePath, FILE_IGNORE_NEW_LINES);
    
    // Estado: ¿estamos dentro de un bloque de código?
    $inCodeBlock = false;
    $codeText = "";
    
    // Estado: ¿estamos dentro de una alerta/bloque citado?
    $inAlert = false;
    $alertType = "";
    $alertText = "";
    
    // Recorrer cada línea del archivo Markdown
    foreach ($lines as $line) {
        $trimmed = trim($line);

        // Manejar bloques de código delimitados por ```
        if (str_starts_with($trimmed, '```')) {
            if ($inCodeBlock) {
                // Terminar bloque de código: renderizar como tabla con fondo gris
                $table = $section->addTable(['borderColor' => 'D0D0D0', 'borderSize' => 6, 'cellMargin' => 120]);
                $table->addRow();
                $cell = $table->addCell(Converter::cmToTwip(16), ['bgColor' => 'F5F5F5']);
                $cell->addText($codeText, ['name' => 'Courier New', 'size' => 9, 'color' => '333333'], ['spaceAfter' => 0]);
                $codeText = "";
                $inCodeBlock = false;
            } else {
                // Iniciar bloque de código: acumular líneas siguientes
                $inCodeBlock = true;
            }
            continue;
        }

        // Si estamos en un bloque de código, acumular la línea actual
        if ($inCodeBlock) {
            $codeText .= $line . "\n";
            continue;
        }

        // Manejar alertas / bloques citados (> [!NOTE], > [!WARNING], etc.)
        if (str_starts_with($trimmed, '>')) {
            $alertContent = ltrim(substr($trimmed, 1));
            // Detectar el tipo de alerta (NOTE, WARNING, IMPORTANT, TIP)
            if (preg_match('/^\[!(NOTE|WARNING|IMPORTANT|TIP)\]/', $alertContent, $matches)) {
                $inAlert = true;
                $alertType = $matches[1];
                $alertText = "";
                continue;
            }
            
            // Acumular líneas de texto dentro de la alerta actual
            if ($inAlert) {
                $alertText .= ($alertText ? " " : "") . $alertContent;
                continue;
            }
        } else {
            if ($inAlert) {
                // La alerta terminó: escribir la caja de alerta acumulada
                writeAlertBox($section, $alertType, $alertText);
                $inAlert = false;
            }
        }

        // Ignorar líneas vacías
        if (empty($trimmed)) {
            continue;
        }

        // Títulos de nivel 1 (#): se ignoran porque ya se usan la portada y Parte I/II
        if (preg_match('/^#\s+(.+)$/', $trimmed, $matches)) {
            continue;
        }
        // Títulos de nivel 2 (##)
        if (preg_match('/^##\s+(.+)$/', $trimmed, $matches)) {
            $section->addTitle(stripMarkdownStyle($matches[1]), 2);
            continue;
        }
        // Títulos de nivel 3 (###)
        if (preg_match('/^###\s+(.+)$/', $trimmed, $matches)) {
            $section->addTitle(stripMarkdownStyle($matches[1]), 3);
            continue;
        }
        // Títulos de nivel 4 (####)
        if (preg_match('/^####\s+(.+)$/', $trimmed, $matches)) {
            $section->addTitle(stripMarkdownStyle($matches[1]), 4);
            continue;
        }

        // Marcadores de Captura de Pantalla: [CAPTURAR PANTALLA: descripción]
        if (preg_match('/^\[CAPTURAR PANTALLA:\s*(.+?)\]$/', $trimmed, $matches)) {
            writeScreenshotPlaceholder($section, $matches[1]);
            continue;
        }

        // Listas con viñetas o numeradas (*, -, 1.)
        if (preg_match('/^(\*|-|\d+\.)\s+(.+)$/', $trimmed, $matches)) {
            $listText = $matches[2];
            // Crear una línea con viñeta y texto formateado
            $textRun = $section->addTextRun(['spaceAfter' => 60, 'marginLeft' => 360]);
            $textRun->addText("•  ", ['bold' => true, 'name' => 'Calibri', 'size' => 11]);
            addFormattedText($textRun, $listText);
            continue;
        }

        // Párrafo normal: texto con formato inline (negrita, etc.)
        $textRun = $section->addTextRun(['spaceAfter' => 120, 'alignment' => Jc::BOTH]);
        addFormattedText($textRun, $trimmed);
    }
    
    // Si el archivo termina mientras estábamos en una alerta, escribirla ahora
    if ($inAlert) {
        writeAlertBox($section, $alertType, $alertText);
    }
}

/**
 * Escribe una caja de alerta estilizada dentro del documento DOCX.
 * 
 * Renderiza una tabla con fondo de color, borde y título según el tipo de alerta:
 * - NOTE → fondo gris claro, título "NOTA"
 * - WARNING → fondo amarillo claro, título "ADVERTENCIA"
 * - IMPORTANT → fondo rojo claro, título "IMPORTANTE"
 * - TIP → fondo verde claro, título "SUGERENCIA"
 *
 * @param object $section Objeto de sección de PhpWord.
 * @param string $type    Tipo de alerta (NOTE, WARNING, IMPORTANT, TIP).
 * @param string $text    Contenido de texto de la alerta.
 */
function writeAlertBox($section, $type, $text) {
    // Colores por defecto para tipo NOTE
    $bgColor = 'F2F4F7';
    $borderColor = 'A2A9B1';
    $title = 'NOTA';
    
    // Sobrescribir colores según el tipo de alerta
    if ($type === 'WARNING') {
        $bgColor = 'FFF9E6';
        $borderColor = 'FFC107';
        $title = 'ADVERTENCIA';
    } elseif ($type === 'IMPORTANT') {
        $bgColor = 'FDF2F2';
        $borderColor = 'F05252';
        $title = 'IMPORTANTE';
    } elseif ($type === 'TIP') {
        $bgColor = 'F3FBF7';
        $borderColor = '0E9F6E';
        $title = 'SUGERENCIA';
    }

    // Crear tabla con borde de color y fondo de celda
    $table = $section->addTable(['borderColor' => $borderColor, 'borderSize' => 12, 'cellMargin' => 160]);
    $table->addRow();
    $cell = $table->addCell(Converter::cmToTwip(16), ['bgColor' => $bgColor]);
    
    // Título de la alerta en negrita
    $cell->addText($title, ['name' => 'Poppins', 'size' => 10, 'bold' => true, 'color' => '333333'], ['spaceAfter' => 60]);
    // Contenido de la alerta con formato inline
    $textRun = $cell->addTextRun(['spaceAfter' => 0]);
    addFormattedText($textRun, $text);
    
    // Espacio vertical después de la caja de alerta
    $section->addText("", [], ['spaceBefore' => 120, 'spaceAfter' => 0]);
}

/**
 * Escribe un marcador de posición para captura de pantalla en el documento.
 * 
 * Renderiza una tabla con borde punteado azul claro y fondo azul pálido,
 * indicando al lector que debe insertar una imagen en ese lugar.
 *
 * @param object $section    Objeto de sección de PhpWord.
 * @param string $description Descripción visual de lo que debe capturarse.
 */
function writeScreenshotPlaceholder($section, $description) {
    // Tabla con borde punteado azul como marcador visual
    $table = $section->addTable(['borderColor' => '0056B3', 'borderSize' => 12, 'cellMargin' => 200, 'borderStyle' => 'dashed']);
    $table->addRow();
    $cell = $table->addCell(Converter::cmToTwip(16), ['bgColor' => 'F0F8FF']);
    
    // Instrucción principal centrada
    $cell->addText("📷 [CAPTURAR E INSERTAR PANTALLA AQUÍ]", ['name' => 'Poppins', 'size' => 11, 'bold' => true, 'color' => '0056B3'], ['alignment' => Jc::CENTER, 'spaceAfter' => 80]);
    // Descripción de lo que debe capturarse
    $cell->addText("Descripción visual requerida: " . $description, ['name' => 'Calibri', 'size' => 10, 'italic' => true, 'color' => '333333'], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
    
    // Espacio vertical después del marcador
    $section->addText("", [], ['spaceBefore' => 120, 'spaceAfter' => 0]);
}

/**
 * Procesa formato inline en una línea de texto Markdown.
 * 
 * Actualmente soporta:
 * - Negrita: **texto** → renderiza en bold
 * - Texto normal: se renderiza sin formato especial
 *
 * @param object $textRun Objeto TextRun de PhpWord donde se añade el texto.
 * @param string $line   Línea de texto Markdown a procesar.
 */
function addFormattedText($textRun, $line) {
    // Dividir la línea en partes separadas por marcadores de negrita **...**
    $parts = preg_split('/(\*\*.*?\*\*)/', $line, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($parts as $part) {
        // Si la parte está envuelta en **, renderizarla en negrita
        if (str_starts_with($part, '**') && str_ends_with($part, '**')) {
            $text = substr($part, 2, -2);
            $textRun->addText($text, ['name' => 'Calibri', 'size' => 11, 'bold' => true, 'color' => '222222']);
        } else {
            // Texto normal sin formato especial
            $textRun->addText($part, ['name' => 'Calibri', 'size' => 11, 'color' => '333333']);
        }
    }
}

/**
 * Elimina los marcadores de estilo Markdown de un texto.
 * 
 * Remueve los asteriscos usados para negrita (**...) y cursiva (*...).
 *
 * @param string $text Texto con posibles marcadores Markdown.
 * @return string Texto limpio sin marcadores de estilo.
 */
function stripMarkdownStyle($text) {
    return str_replace(['**', '*'], '', $text);
}

// --- Ejecutar la compilación con las rutas de los manuales ---
compileMarkdownToDocx(
    'C:\\Users\\gabriel\\.gemini\\antigravity\\brain\\984719b3-e594-4b3f-86a4-3f0ad8d0ffe6\\user-manual.md',
    'C:\\Users\gabriel\\.gemini\\antigravity\\brain\\984719b3-e594-4b3f-86a4-3f0ad8d0ffe6\\system-manual.md',
    'c:\\laragon\\www\\Betelcreativa\\docs\\Manual_BetelCreativa.docx'
);
