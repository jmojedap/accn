<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    :root {
        --primary-gradient: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        --primary-color: #6366f1;
        --secondary-bg: #F9FAFB;
        --white: #FFFFFF;
        --text-dark: #1F2937;
        --text-muted: #6B7280;
        --border-light: #E5E7EB;
        --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --radius-lg: 16px;
        --radius-xl: 24px;
    }

    body {
        font-family: 'Inter', sans-serif;
        background-color: #F3F4F6;
        color: var(--text-dark);
    }

    /* Main Layout */
    .bg-light-gray {
        background-color: #F3F4F6;
    }

    .chat-container {
        width: 100%;
        max-width: 900px;
        height: 85vh;
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid var(--border-light);
        margin: 20px;
    }

    /* Header */
    .chat-header {
        padding: 16px 24px;
        background: var(--white);
        border-bottom: 1px solid var(--border-light);
        z-index: 10;
    }

    .header-icon-bg {
        width: 40px;
        height: 40px;
        background: #EEF2FF;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .btn-tool {
        width: 32px;
        height: 32px;
        border: none;
        background: transparent;
        border-radius: 50%;
        color: var(--text-muted);
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-tool:hover {
        background-color: #F3F4F6;
        color: var(--text-dark);
    }

    /* Messages Area */
    .chat-messages {
        flex: 1;
        padding: 24px;
        overflow-y: auto;
        background-color: #F8FAFC;
        background-image: radial-gradient(#E2E8F0 1px, transparent 1px);
        background-size: 24px 24px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* Message Row Layout */
    .message-row {
        display: flex;
        align-items: flex-end;
        gap: 12px;
        max-width: 80%;
        animation: slideIn 0.3s ease-out forwards;
        opacity: 0;
        transform: translateY(10px);
    }

    @keyframes slideIn {
        to { opacity: 1; transform: translateY(0); }
    }

    .message-row.user {
        align-self: flex-end;
        flex-direction: row-reverse;
    }

    .message-row.model {
        align-self: flex-start;
    }

    /* Avatar */
    .message-avatar img {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--white);
        box-shadow: var(--shadow-sm);
    }

    .message-row .btn-tool {
        color: #9CA3AF;
        font-size: 0.7rem;
        width: 24px;
        height: 24px;
    }

    .message-row .btn-tool:hover {
        background-color: #F3F4F6;
        color: var(--text-dark);
    }   

    /* Bubble */
    .chat-bubble {
        padding: 14px 18px;
        border-radius: 18px;
        font-size: 0.95rem;
        line-height: 1.6;
        word-wrap: break-word;
        position: relative;
    }

    .chat-bubble.user {
        background: var(--primary-gradient);
        color: var(--white);
        border-bottom-right-radius: 4px;
    }

    .chat-bubble.user p {
        margin-bottom: 0;
    }
    
    .chat-bubble.user a {
        color: white;
        text-decoration: underline;
    }

    .chat-bubble.model {
        background: var(--white);
        color: var(--text-dark);
        border-bottom-left-radius: 4px;
    }

    .chat-bubble.model p {
        margin-bottom: 0.5rem;
    }
    .chat-bubble.model p:last-child {
        margin-bottom: 0;
    }

    /* Input Area */
    .chat-input-area {
        padding: 20px 24px;
        background: var(--white);
        border-top: 1px solid var(--border-light);
    }

    .input-wrapper {
        flex: 1;
        background: #F3F4F6;
        border-radius: 24px;
        padding: 8px 16px;
        transition: all 0.2s;
        border: 2px solid transparent;
        display: flex;
        align-items: center;
    }

    .input-wrapper:focus-within {
        background: var(--white);
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .custom-textarea {
        border: none;
        background: transparent;
        padding: 8px 0;
        resize: none;
        box-shadow: none;
        font-size: 0.95rem;
        max-height: 150px;
        overflow-y: auto;
    }

    .custom-textarea:focus {
        box-shadow: none;
        background: transparent;
    }

    /* Scrollbar for textarea */
    .custom-textarea::-webkit-scrollbar {
        width: 4px;
    }
    .custom-textarea::-webkit-scrollbar-thumb {
        background-color: #CBD5E1;
        border-radius: 4px;
    }

    .btn-send {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        border: none;
        background: var(--primary-gradient);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
    }

    .btn-send:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
    }

    .btn-send:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        background: #CBD5E1;
    }

    /* Typing Indicator */
    .typing-indicator {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 8px 16px;
        background: white;
        border-radius: 20px;
        box-shadow: var(--shadow-sm);
    }

    .typing-indicator span {
        width: 6px;
        height: 6px;
        background-color: #9CA3AF;
        border-radius: 50%;
        animation: bounce 1.4s infinite ease-in-out both;
    }

    .typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
    .typing-indicator span:nth-child(2) { animation-delay: -0.16s; }

    @keyframes bounce {
        0%, 80%, 100% { transform: scale(0); }
        40% { transform: scale(1); }
    }

    /* Markdown Styles for Model Messages */
    .chat-bubble pre {
        background: #1f2937;
        color: #f3f4f6;
        padding: 12px;
        border-radius: 8px;
        margin: 10px 0;
        overflow-x: auto;
    }
    
    .chat-bubble code {
        font-family: 'Menlo', 'Monaco', 'Courier New', monospace;
        font-size: 0.9em;
        background: rgba(0,0,0,0.05);
        padding: 2px 4px;
        border-radius: 4px;
    }
    
    .chat-bubble pre code {
        background: transparent;
        padding: 0;
        color: inherit;
    }

    /* Mobile Responsiveness */
    @media (max-width: 768px) {
        .chat-container {
            margin: 0;
            height: 100vh;
            max-width: 100%;
            border-radius: 0;
            border: none;
        }
        
        .message-row {
            max-width: 90%;
        }
    }
</style>