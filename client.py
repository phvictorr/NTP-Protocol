import requests
import time
import socket

def sync_time(api_url='http://www.tecstudio.com.br/wp-json/time/v1/current'):
    device_name = socket.gethostname()  # Identificar o dispositivo
    for _ in range(4):  # Tentar 4 vezes
        send_time = time.time()  # Captura o tempo de envio
        
        try:
            response = requests.get(api_url)
            receive_time = time.time()  # Tempo de recebimento da resposta
            
            data = response.json()
            if 'error' in data:
                print(f"[{device_name}] Erro na API: {data['error']}")
                continue

            # Extraindo timestamps da API
            ntp_time = data['ntp_time']
            response_timestamp = data['response_timestamp']

            # Calculando o atraso da rede (Algoritmo de Cristian)
            network_delay = (receive_time - send_time) - (response_timestamp - ntp_time)
            adjusted_time = ntp_time + (network_delay / 2)  # Ajuste da hora

            # Exibir os resultados para este dispositivo
            print(f"[{device_name}] Atraso: {network_delay:.3f} s")
            print(f"[{device_name}] Hora ajustada: {time.strftime('%Y-%m-%d %H:%M:%S', time.gmtime(adjusted_time))}")
            
            time.sleep(5)  # Atualização periódica
        except requests.exceptions.RequestException as e:
            print(f"[{device_name}] Erro ao sincronizar: {e}")

if __name__ == "__main__":
    sync_time()