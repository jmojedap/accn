<?= view('m/chat/conversation/style') ?>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.5/dist/purify.min.js"></script>

<div id="conversationApp" class="d-flex justify-content-center align-items-center min-vh-100 bg-light-gray">
    
    <div class="chat-container">
        <!-- Header -->
        <div class="chat-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="header-icon-bg me-3">
                    <img class="w-100 h-100 rounded-circle sqr-180" src="<?= URL_CONTENT ?>uploads/2026/01/sm_2197_20260130094424580.jpg" alt="AI">
                </div>
                <div>
                    <h5 class="mb-0 fw-bold text-dark">Diana López</h5>
                    <small class="text-muted">Siempre disponible para ayudarte</small>
                </div>
            </div>
            
            <div class="dropdown">
                <button class="btn-tool" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 radius-12">
                    <li><a class="dropdown-item text-danger" href="#" @click="selectable = !selectable">
                            <i class="fa fa-check-square me-2"></i>Seleccionar mensajes
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="chat-messages" id="chat-messages" ref="chatMessages">
            <div v-for="(message, index) in messages" :key="index" class="message-row" :class="message.role">
                <div class="message-checkbox" v-show="selectable">
                    <input type="checkbox" v-model="selected" :value="message.id">
                </div>
                <!-- Avatar -->
                <div class="message-avatar">
                   <img v-if="message.role == 'model'" src="<?= URL_CONTENT ?>uploads/2026/01/sm_2197_20260130094424580.jpg" alt="AI">
                   <img v-else src="<?= URL_RESOURCES ?>images/users/sm_user.png" alt="User">
                </div>

                <!-- Bubble -->
                <div class="message-content">
                    <div class="chat-bubble shadow-sm" :class="message.role">
                        <div v-html="markdownToHtml(message.text)"></div>
                    </div>
                </div>
                <div class="dropdown">
                    <button class="btn-tool" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" @click="setSelected(message.id)" data-bs-toggle="modal" data-bs-target="#modalDeleteSelected">Borrar</a></li>
                    </ul>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div class="text-center py-3" v-show="loading">
                <div class="typing-indicator">
                    <span></span><span></span><span></span>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="chat-input-area" v-show="!selectable">
            <form id="ia-chat-form" @submit.prevent="handleSubmit" class="w-100">
                <fieldset v-bind:disabled="loading" class="d-flex w-100 align-items-end gap-2">
                    <input type="hidden" name="conversation_id" id="conversation_id" v-model="conversationId">
                    
                    <div class="input-wrapper shadow-sm">
                        <textarea 
                            name="prompt" 
                            id="user-input" 
                            v-model="prompt" 
                            rows="1"
                            @input="autoExpand($event)"
                            @keydown.enter="handleKeyDown"
                            required
                            placeholder="Escribe tu mensaje aquí..."
                            class="form-control custom-textarea"
                        ></textarea>
                    </div>

                    <button type="submit" class="btn-send shadow-sm" :disabled="!prompt.trim() && !loading">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </fieldset>
            </form>
        </div>

        <!-- ÁREA DE HERRAMIENTAS -->
         <div v-show="selectable == true">
             <div class="tools-area d-flex justify-content-between align-items-center p-3">
                 <button class="btn-tool" @click="selectable = !selectable">
                     <i class="fas fa-times"></i>
                 </button>
                 <button class="btn-tool" data-bs-toggle="modal" data-bs-target="#modalDeleteSelected"
                     v-bind:title="'Borrar ' + selected.length + ' ' + entityInfo.plural"
                     v-bind:disabled="selected.length == 0">
                     <i class="far fa-trash-alt"></i>
                 </button>
             </div>
         </div>
    </div>
    <?= view('common/bs5/modal_delete_selected') ?>
</div>
<?= view('m/chat/conversation/vue') ?>