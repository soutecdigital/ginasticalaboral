<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function confirmarPresenca(escalaId, latDestino, lonDestino) {
        // 1. Verifica se o navegador suporta geolocalização
        if (!navigator.geolocation) {
            Swal.fire('Erro', 'Seu celular não suporta geolocalização.', 'error');
            return;
        }

        // Feedback visual de carregamento
        Swal.fire({
            title: 'Validando localização...',
            text: 'Por favor, aguarde enquanto confirmamos sua posição.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // 2. Captura a posição atual do usuário
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const latUser = position.coords.latitude;
                const lonUser = position.coords.longitude;

                // 3. Envia os dados para o Controller via Fetch (AJAX)
                enviarCheckin(escalaId, latUser, lonUser);
            },
            (error) => {
                let msg = 'Erro ao obter localização.';
                if (error.code === 1) msg = 'Você precisa permitir o acesso ao GPS.';
                Swal.fire('Ops!', msg, 'warning');
            }, {
                enableHighAccuracy: true,
                timeout: 10000
            }
        );
    }

    function enviarCheckin(id, lat, lon) {
        // Substitua pela sua rota real de check-in
        const url = `/professor/checkin/${id}`;

        fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    latitude: lat,
                    longitude: lon
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Presença Confirmada!',
                        text: data.message,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload(); // Recarrega para mostrar o status de confirmado
                    });
                } else {
                    Swal.fire('Erro', data.message || 'Você está fora do raio permitido.', 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire('Erro', 'Falha ao conectar com o servidor.', 'error');
            });
    }
</script>
