<script>
// VueApp
//-----------------------------------------------------------------------------
var conversationApp = createApp({
    data(){
        return{
            conversationId: <?= (int) $conversation['id'] ?>,
            messages: <?= json_encode($messages, JSON_UNESCAPED_UNICODE) ?>,
            prompt: '',
            loading: false,
            deleting: false,
            selectable: false,
            selected: [],
            chaterInfo: {
                name: <?= json_encode($sitAgent->title ?? 'Chat', JSON_UNESCAPED_UNICODE) ?>,
                picture: <?= json_encode($sitAgent->url_thumbnail ?? '', JSON_UNESCAPED_UNICODE) ?>
            },
            deleteConfirmationTexts : {
                title: 'Borrar mensajes',
                text: 'Confirmas la eliminacion de los mensajes seleccionados?',
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
            const promptText = this.prompt.trim();

            if (promptText === '' || this.loading) {
                return;
            }

            const userMessage = this.addNewMessage({
                role: 'user',
                text: promptText,
            });

            this.prompt = '';
            this.resetTextarea();
            this.getResponse(promptText, userMessage);
        },
        getResponse: function(promptText, userMessage) {
            this.loading = true;

            const payload = {
                prompt: promptText,
                conversation_id: this.conversationId,
            };

            axios.post(URL_API + 'conversations/get_answer/', payload)
            .then(response => {
                const data = response.data || {};

                if (userMessage && data.user_message_id) {
                    userMessage.id = data.user_message_id;
                }

                const responseText = data.response_text || 'No fue posible obtener respuesta.';

                this.addNewMessage({
                    id: data.ai_message_id || null,
                    role: 'model',
                    text: responseText,
                });

                if (data.error) {
                    console.error(data.error);
                }
            })
            .catch(error => {
                console.error(error);
                this.addNewMessage({
                    role: 'model',
                    text: 'Error al obtener la respuesta del chat.',
                });
            })
            .finally(() => {
                this.loading = false;
                // El textarea está dentro de un fieldset que se deshabilita
                // durante la respuesta. Esperamos a que Vue actualice el DOM
                // antes de devolverle el foco.
                this.$nextTick(() => {
                    this.focusInput();
                });
            });
        },
        markdownToHtml: function(markdownText) {
            const text = markdownText ? String(markdownText) : '';

            if (typeof marked === 'undefined') {
                return text;
            }

            const rawHtml = marked.parse(text);

            return window.DOMPurify
                ? DOMPurify.sanitize(rawHtml)
                : rawHtml;
        },
        addNewMessage(newMessage) {
            if (!newMessage.local_id) {
                newMessage.local_id = 'local-' + Date.now() + '-' + Math.random().toString(36).slice(2);
            }

            this.messages.push(newMessage);

            this.$nextTick(() => {
                this.aplicarFadeInUltimoMensaje();
                this.scrollToDown();
                this.focusInput();
            });

            return newMessage;
        },
        aplicarFadeInUltimoMensaje() {
            const chatContainer = document.getElementById('chat-messages');

            if (!chatContainer) {
                return;
            }

            const mensajes = chatContainer.querySelectorAll('.message-row');
            const ultimoMensaje = mensajes[mensajes.length - 1];

            if (ultimoMensaje) {
                ultimoMensaje.classList.add('fade-enter');
                void ultimoMensaje.offsetWidth;

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
        focusInput() {
            const input = this.$refs.userInput || document.getElementById('user-input');

            if (input && !this.selectable && !this.loading) {
                input.focus();

                const cursorPosition = input.value.length;
                input.setSelectionRange(cursorPosition, cursorPosition);
            }
        },
        resetTextarea() {
            const textarea = document.getElementById('user-input');

            if (textarea) {
                textarea.style.height = 'auto';
            }
        },
        handleKeyDown(event) {
            if (event.shiftKey) {
                return;
            }

            event.preventDefault();

            if (this.prompt.trim() !== '' && !this.loading) {
                this.handleSubmit();
            }
        },
        messageClass(message){
            return message.role === 'user' ? 'chat-pregunta' : 'chat-respuesta';
        },
        autoExpand(event) {
            const textarea = event.target;
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
        },
        setSelected: function(messageId){
            if (!messageId) {
                return;
            }

            this.selected = [messageId];
        },
        deleteSelected: function(){
            const selectedIds = this.selected.filter(Boolean);

            if (selectedIds.length === 0 || this.deleting) {
                return;
            }

            this.deleting = true;

            const formValues = {
                selected: selectedIds,
                conversation_id: this.conversationId
            };

            axios.delete(URL_API + 'ai_generate/delete_messages/', { data: formValues })
            .then(response => {
                if (response.data.qty_deleted > 0) {
                    if (window.toastr) {
                        toastr.success('Mensajes eliminados');
                    }

                    this.hideDeletedMessages(selectedIds);
                } else if (window.toastr) {
                    toastr.warning('No se eliminaron mensajes');
                }
            })
            .catch(error => {
                console.error(error);

                if (window.toastr) {
                    toastr.error('No fue posible eliminar los mensajes');
                }
            })
            .finally(() => {
                this.deleting = false;
                this.hideDeleteModal();
            });
        },
        hideDeletedMessages: function(selectedIds){
            const deletedIds = new Set(selectedIds.map(id => String(id)));

            this.messages = this.messages.filter(message => !deletedIds.has(String(message.id)));
            this.selected = [];
        },
        hideDeleteModal: function(){
            const modalElement = document.getElementById('modalDeleteSelected');

            if (window.bootstrap && modalElement) {
                const modal = bootstrap.Modal.getInstance(modalElement);

                if (modal) {
                    modal.hide();
                    return;
                }
            }

            if (window.jQuery) {
                $('#modalDeleteSelected').modal('hide');
            }
        }
    },
    mounted(){
        this.$nextTick(() => {
            this.scrollToDown();
            this.focusInput();
        });
    }
}).mount('#conversationApp');
</script>