<?php
/**
 * Configura el token CSRF para todos los clientes AJAX cargados por las plantillas.
 *
 * Debe incluirse después de cargar Axios y jQuery. El filtro CsrfToken devuelve
 * el token vigente en cada respuesta para mantener sincronizado el navegador.
 */
$csrfHeader = config('Security')->headerName;
$csrfHash = csrf_hash();
?>

<meta name="csrf-header" content="<?= esc($csrfHeader) ?>">
<meta name="csrf-token" content="<?= esc($csrfHash) ?>">

<script>
/**
 * Instala el token CSRF en Axios y jQuery para todas las mutaciones HTTP.
 */
(function configureCsrfClients() {
    const headerName = <?= json_encode($csrfHeader) ?>;
    let token = <?= json_encode($csrfHash) ?>;

    /** Actualiza el token usado por solicitudes posteriores. */
    function updateCsrfToken(nextToken) {
        if (!nextToken) {
            return;
        }

        token = nextToken;
        axios.defaults.headers.common[headerName] = token;

        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        if (tokenMeta) {
            tokenMeta.setAttribute('content', token);
        }
    }

    updateCsrfToken(token);

    // Captura el token devuelto tanto en respuestas exitosas como fallidas.
    axios.interceptors.response.use(
        function onCsrfResponse(response) {
            updateCsrfToken(response.headers[headerName.toLowerCase()]);
            return response;
        },
        function onCsrfError(error) {
            if (error.response) {
                updateCsrfToken(error.response.headers[headerName.toLowerCase()]);
            }

            return Promise.reject(error);
        }
    );

    // Añade el mismo encabezado a los módulos heredados que todavía usan jQuery.
    if (window.jQuery) {
        window.jQuery(document).ajaxSend(function addCsrfToJquery(event, xhr) {
            xhr.setRequestHeader(headerName, token);
        });

        window.jQuery(document).ajaxComplete(function refreshCsrfFromJquery(event, xhr) {
            updateCsrfToken(xhr.getResponseHeader(headerName));
        });
    }
})();
</script>
