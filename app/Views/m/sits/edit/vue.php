<script>
const initialSit = <?= json_encode($row, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

const editPost = createApp({
    data() {
        return {
            fields: initialSit,
            loading: false,
            arrVisibility: ItemsApp.arrayCategory(37),
        };
    },
    methods: {
        handleSubmit() {
            const form = document.getElementById('editForm');

            if (!form.reportValidity()) {
                return;
            }

            this.loading = true;
            const formData = new FormData(form);

            axios.post(URL_API + 'posts/update/' + this.fields.idcode, formData)
                .then(response => {
                    if (response.data.saved) {
                        toastr['success']('Sit actualizado');
                    } else {
                        toastr['error']('No se pudo actualizar el Sit');
                    }
                })
                .catch(error => {
                    console.log(error);
                    toastr['error']('Ocurrió un error al actualizar el Sit');
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
}).mount('#editPost');
</script>
