// Função fora de contexto (implementada via WordPress REST API)

// Adiciona uma rota de API para fornecer a hora atual usando NTP
add_action('rest_api_init', function () {
    register_rest_route('time/v1', '/current', array(
        'methods'  => 'GET',
        'callback' => 'get_ntp_time',
    ));
});

// Função para obter a hora do servidor NTP e incluir um timestamp de resposta
function get_ntp_time() {
    $ntp_server = 'pool.ntp.org'; // Servidor NTP público
    $port = 123;

    // Criar um socket UDP para conexão com o NTP
    $socket = @fsockopen("udp://$ntp_server", $port, $errno, $errstr, 1);
    
    if (!$socket) {
        return new WP_REST_Response(['error' => "Erro ao conectar ao servidor NTP: $errstr"], 500);
    }

    // Pacote NTP de 48 bytes
    $request = chr(0x1B) . str_repeat("\0", 47);
    fwrite($socket, $request);

    // Ler a resposta (48 bytes)
    $response = fread($socket, 48);
    fclose($socket);

    if (strlen($response) < 48) {
        return new WP_REST_Response(['error' => 'Resposta inválida do servidor NTP'], 500);
    }

    // Extrair o timestamp NTP (bytes 40 a 43)
    $data = unpack('Ntime', substr($response, 40, 4));
    $ntp_time = $data['time'] - 2208988800; // Ajuste do Epoch do NTP para Unix

    // Captura o timestamp no momento da resposta da API
    $response_timestamp = time();

    return new WP_REST_Response([
        'ntp_time' => $ntp_time,
        'response_timestamp' => $response_timestamp
    ], 200);
}
