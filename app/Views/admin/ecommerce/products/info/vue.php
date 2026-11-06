<script>
(function () {
    const product = <?= json_encode($row, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) ?>;
    createApp({
        data() {
            return {product, imageFailed: false};
        },
        computed: {
            kindName() { return ({digital: 'Virtual', service: 'Servicio', access: 'Acceso', physical: 'Físico'})[this.product.product_kind] || this.product.product_kind; },
            statusName() { return ({0: 'Borrador', 1: 'Activo', 2: 'Archivado'})[this.product.status] || 'Sin estado'; },
            statusClass() { return ({0: 'bg-light text-dark border', 1: 'bg-success', 2: 'bg-secondary'})[this.product.status] || 'bg-secondary'; },
            imageUrl() {
                try {
                    const url = new URL(this.product.image_url);
                    return ['http:', 'https:'].includes(url.protocol) ? url.href : '';
                } catch (error) {
                    return '';
                }
            },
        },
        methods: {
            money(value) {
                try {
                    return new Intl.NumberFormat('es-CO', {style: 'currency', currency: this.product.currency, maximumFractionDigits: 2}).format(Number(value));
                } catch (error) {
                    return value;
                }
            },
            dateText(value) { return value ? String(value).split('.')[0] : 'No especificada'; },
        },
    }).mount('#productInfoApp');
})();
</script>
