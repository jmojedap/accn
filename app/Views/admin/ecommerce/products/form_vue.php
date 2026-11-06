<script>
(function () {
    const config = <?= json_encode(['mode' => $formMode, 'row' => $row, 'url' => $productsUrl, 'apiUrl' => $productsApiUrl], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) ?>;
    const defaults = {name: '', product_kind: 'digital', status: '0', description: '', meta_description: '', image_url: '', base_price: '0.00', currency: 'COP', tax_percent: '0.00', cost: '0.00', idcode: '', slug: ''};
    let createdModal = null;
    createApp({
        data() {
            return {
                creating: config.mode === 'create',
                fields: {...defaults, ...(config.row || {})},
                currentIdCode: config.row ? config.row.idcode : '',
                rowId: config.row ? config.row.id : 0,
                loading: false, error: '', errors: {}, savedNotice: '',
            };
        },
        mounted() {
            if (this.creating) createdModal = new bootstrap.Modal(document.getElementById('productCreatedModal'), {backdrop: 'static', keyboard: false});
        },
        beforeUnmount() {
            if (createdModal) createdModal.dispose();
        },
        computed: {
            createdInfoUrl() { return config.url + 'info/' + encodeURIComponent(this.rowId); },
        },
        methods: {
            async handleSubmit() {
                if (this.loading) return;
                const form = document.getElementById('productForm');
                const payload = new FormData(form);
                this.loading = true;
                this.error = '';
                this.errors = {};
                this.savedNotice = '';
                const endpoint = this.creating ? 'create' : 'update/' + encodeURIComponent(this.currentIdCode);
                try {
                    const response = await axios.post(config.apiUrl + endpoint, payload);
                    if (!response.data.savedId) {
                        this.errors = response.data.errors || {};
                        this.error = 'No se guardó el producto. Revisa los campos e intenta de nuevo.';
                        return;
                    }
                    this.rowId = response.data.savedId;
                    this.currentIdCode = response.data.idcode;
                    this.fields.idcode = response.data.idcode;
                    if (this.creating) {
                        createdModal.show();
                    } else {
                        this.savedNotice = 'Cambios guardados correctamente.';
                    }
                } catch (error) {
                    const response = error.response;
                    this.errors = response && response.data && response.data.errors && typeof response.data.errors === 'object' ? response.data.errors : {};
                    if (response && response.status === 422) this.error = 'Revisa los campos marcados.';
                    else if (response && response.status === 403) this.error = 'Tu sesión venció o no tienes permiso. Recarga la página para continuar.';
                    else if (response && response.status === 404) this.error = 'El producto ya no está disponible. Vuelve al listado.';
                    else this.error = 'No fue posible guardar el producto. Intenta de nuevo.';
                    this.$nextTick(() => {
                        const invalid = form.querySelector('.is-invalid');
                        if (invalid) invalid.focus();
                    });
                } finally {
                    this.loading = false;
                }
            },
            createAnother() {
                createdModal.hide();
                this.fields = {...defaults};
                this.rowId = 0;
                this.currentIdCode = '';
                this.errors = {};
                this.error = '';
                this.savedNotice = '';
                this.$nextTick(() => document.getElementById('productName').focus());
            },
        },
    }).mount('#productFormApp');
})();
</script>
