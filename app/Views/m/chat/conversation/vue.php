<script>
// VueApp
//-----------------------------------------------------------------------------
var conversationApp = createApp({
    data(){
        return{
            conversationId: 1,
            userId: 1,
            messages: <?php echo json_encode($messages); ?>,
            prompt: '',
            loading: false,
            deleting: false,
            selectable: false,
            selected: [],
            respuesta:'',
            htmlResponse: '',
            chaterInfo: {
                name: 'Secretaria Médica',
                picture: '<?= URL_CONTENT ?>uploads/2026/01/sm_2197_20260130094424580.jpg'
            },
            deleteConfirmationTexts : {
                title: 'Borrar mensajes',
                text: '¿Confirma la eliminación de todos los mensajes?',
                buttonText: 'Eliminar'
            },
            entityInfo: {
                plural: 'mensajes',
                singular: 'mensaje' 
            }
        }
    },
    methods: {
        handleSubmit: function(){
            this.loading = true
            var newMessage = {
                role:'user',
                text: this.prompt,
            }
            this.addNewMessage(newMessage)
            this.getResponse()
        },
        getResponse: function() {
            if (!this.prompt) {
                console.warn("El input está vacío");
                return;
            }

            this.loading = true;

            const payload = {
                prompt: this.prompt.trim(),
                conversation_id: this.conversationId,
                system_instruction_key: 'secretaria-medica' 
            };
            
            this.prompt = ''; // Limpiar el input del usuario antes de enviar

            axios.post(URL_API + 'ai_generate/get_answer/', payload)
            .then(response => {
                this.respuesta = response.data.responseText ?? 'Ocurrió un error al obtener la respuesta.';
                this.htmlResponse = this.markdownToHtml(this.respuesta);

                var newMessage = {
                    role:'model',
                    text: this.htmlResponse
                }
                this.addNewMessage(newMessage)
                this.loading = false;
                this.prompt = '';
            })
            .catch(error => {
                console.error(error);
                this.htmlResponse = '<p>Error al obtener la respuesta del Chat.</p>';
            });
        },
        // Convertir respuesta de Markdown a HTML
        markdownToHtml: function(markdownText) {
            // Convertir markdown a HTML
            const rawHtml = marked.parse(markdownText); //

            // Sanitizar si DOMPurify está disponible
            var htmlResponse = window.DOMPurify
                ? DOMPurify.sanitize(rawHtml)
                : rawHtml;

            return htmlResponse;
        },
        addNewMessage(newMessage) {
            this.messages.push(newMessage);

            this.$nextTick(() => {
                this.aplicarFadeInUltimoMensaje();
                this.scrollToDown();
                document.getElementById('user-input').focus();
            });
        },
        aplicarFadeInUltimoMensaje() {
            const chatContainer = document.getElementById('chat-messages');
            const mensajes = chatContainer.querySelectorAll('.chat-mensaje');
            const ultimoMensaje = mensajes[mensajes.length - 1];

            if (ultimoMensaje) {
                ultimoMensaje.classList.add('fade-enter');
                void ultimoMensaje.offsetWidth; // Forzar reflow

                setTimeout(() => {
                    ultimoMensaje.classList.remove('fade-enter');
                }, 20);
            }
        },
        scrollToDown() {
            const chatContainer = document.getElementById('chat-messages');
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        },
        handleKeyDown(event) {
            if (!event.shiftKey) {
                event.preventDefault();
                if (this.prompt.trim() !== '') {
                    this.handleSubmit();
                }
            }
        },
        messageClass(message){
            if ( message.role == 'user') {
                return 'chat-pregunta'
            }
            return 'chat-respuesta'
        },
        autoExpand(event) {
            const textarea = event.target;
            textarea.style.height = 'auto'; // Resetear altura previa
            textarea.style.height = textarea.scrollHeight + 'px'; // Ajustar a contenido
        },
        setSelected: function(messageId){
            this.selected = []
            this.selected.push(messageId)
        },
        deleteSelected: function(){
            this.loading = true
            var formValues = {
                selected: this.selected,
                conversation_id: this.conversationId
            }
            axios.delete(URL_API + 'ai_generate/delete_messages/', { data: formValues })
            .then(response => {
                if ( response.data.qty_deleted > 0 ) {
                    toastr['success']('Mensajes eliminados')
                    this.hideDeletedMessages()
                } else {
                    toastr['warning']('No se eliminaron mensajes')
                }
                //Ocultar modal
                $('#modalDeleteSelected').modal('hide') 
                this.loading = false
            })
            .catch( function(error) {console.log(error)} )
        },
        hideDeletedMessages: function(){
            //Ocultar mensajes eliminados
            this.messages = this.messages.filter(message => message.id !== this.selected[0])
            this.selected = []
        }
    },
    mounted(){
        //this.getList()
        this.$nextTick(() => {
            this.scrollToDown();
            document.getElementById('user-input').focus();
        });
    }
}).mount('#conversationApp');
</script>