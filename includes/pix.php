<?php
/**
 * Funções para geração de código PIX
 * Padrão EMV (Europay, Mastercard e Visa)
 */

/**
 * Gera código PIX copia e cola
 */
function gerarCodigoPix($valor, $chave, $beneficiario, $cidade, $txid = null) {
    // Remover acentos e caracteres especiais
    $beneficiario = removerAcentos($beneficiario);
    $cidade = removerAcentos($cidade);
    
    // Gerar ID da transação se não fornecido
    if (!$txid) {
        $txid = 'SORTE' . strtoupper(substr(uniqid(), -8));
    }
    
    // Payload Format Indicator
    $payload = '000201'; // Versão do payload
    
    // Merchant Account Information
    $payload .= '26' . strlen('0014br.gov.bcb.pix' . '01' . strlen($chave) . $chave);
    $payload .= '0014br.gov.bcb.pix';
    $payload .= '01' . strlen($chave) . $chave;
    
    // Merchant Category Code
    $payload .= '52040000'; // 0000 = não especificado
    
    // Transaction Currency (986 = BRL)
    $payload .= '5303986';
    
    // Transaction Amount
    $valorFormatado = number_format($valor, 2, '.', '');
    $payload .= '54' . strlen($valorFormatado) . $valorFormatado;
    
    // Country Code
    $payload .= '5802BR';
    
    // Merchant Name
    $payload .= '59' . strlen($beneficiario) . $beneficiario;
    
    // Merchant City
    $payload .= '60' . strlen($cidade) . $cidade;
    
    // Additional Data Field Template
    $payload .= '62' . strlen('05' . strlen($txid) . $txid);
    $payload .= '05' . strlen($txid) . $txid;
    
    // CRC16
    $payload .= '6304';
    $crc = calcularCRC16($payload);
    $payload .= $crc;
    
    return $payload;
}

/**
 * Calcula CRC16 CCITT
 */
function calcularCRC16($payload) {
    $polynomial = 0x1021;
    $crc = 0xFFFF;
    
    for ($i = 0; $i < strlen($payload); $i++) {
        $crc ^= (ord($payload[$i]) << 8);
        
        for ($j = 0; $j < 8; $j++) {
            if (($crc & 0x8000) != 0) {
                $crc = (($crc << 1) ^ $polynomial);
            } else {
                $crc = ($crc << 1);
            }
        }
    }
    
    $crc = $crc & 0xFFFF;
    return strtoupper(dechex($crc));
}

/**
 * Remove acentos e caracteres especiais
 */
function removerAcentos($string) {
    $string = strtoupper($string);
    
    $acentos = [
        'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A',
        'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
        'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
        'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
        'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
        'Ç' => 'C', 'Ñ' => 'N'
    ];
    
    $string = strtr($string, $acentos);
    
    // Remover caracteres não alfanuméricos (exceto espaço)
    $string = preg_replace('/[^A-Z0-9 ]/', '', $string);
    
    return $string;
}

/**
 * Valida chave PIX
 */
function validarChavePix($chave, $tipo) {
    switch ($tipo) {
        case 'email':
            return filter_var($chave, FILTER_VALIDATE_EMAIL) !== false;
        
        case 'cpf':
            $chave = preg_replace('/[^0-9]/', '', $chave);
            return strlen($chave) === 11;
        
        case 'cnpj':
            $chave = preg_replace('/[^0-9]/', '', $chave);
            return strlen($chave) === 14;
        
        case 'telefone':
            $chave = preg_replace('/[^0-9]/', '', $chave);
            return strlen($chave) >= 10 && strlen($chave) <= 11;
        
        case 'aleatoria':
            // Chave aleatória pode ter 32 caracteres (sem hífens) ou formato UUID
            $chave = str_replace('-', '', $chave);
            return strlen($chave) === 32;
        
        default:
            return false;
    }
}
