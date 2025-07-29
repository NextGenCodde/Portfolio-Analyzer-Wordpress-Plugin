<?php
/*
Plugin Name: Advanced AI Portfolio Analyzer Pro
Description: Professional AI-powered PSX portfolio analysis tool with modern ChatGPT-style interface. Insert via shortcode [ai_portfolio_analyzer].
Version: 3.1
Author: Faizan
*/


// ----------- ENQUEUE SCRIPTS AND STYLES -----------
add_action('wp_enqueue_scripts', function() {
    if (has_shortcode(get_post()->post_content ?? '', 'ai_portfolio_analyzer')) {
        wp_enqueue_script('chart-js', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js', [], '3.9.1', true);
        wp_enqueue_script('ai-pa-script', plugin_dir_url(__FILE__) . 'ai-portfolio-analyzer.js', ['jquery'], '3.1', true);
        wp_localize_script('ai-pa-script', 'aiPA', [
            'ajax_url' => rest_url('ai-pa/v1/analyze'),
            'nonce' => wp_create_nonce('wp_rest')
        ]);
    }
});

// ----------- SETTINGS MENU -----------
add_action('admin_menu', 'ai_portfolio_analyzer_admin_menu');

function ai_portfolio_analyzer_admin_menu() {
    add_options_page(
        'AI Portfolio Analyzer Settings',
        'AI Portfolio Analyzer',
        'manage_options',
        'ai-portfolio-analyzer',
        'ai_portfolio_analyzer_settings_page'
    );
    
    add_menu_page(
        'AI Portfolio Analyzer',
        'Portfolio AI',
        'manage_options',
        'ai-portfolio-main',
        'ai_portfolio_analyzer_settings_page',
        'dashicons-chart-line',
        30
    );
}

function ai_portfolio_analyzer_settings_page() {
    if (isset($_POST['submit'])) {
        update_option('ai_pa_api_key', sanitize_text_field($_POST['ai_pa_api_key']));
        update_option('ai_pa_api_url', sanitize_text_field($_POST['ai_pa_api_url']));
        update_option('ai_pa_theme_color', sanitize_text_field($_POST['ai_pa_theme_color']));
        echo '<div class="updated"><p>Settings Saved Successfully!</p></div>';
    }
    
    $api_key = get_option('ai_pa_api_key', '');
    $api_url = get_option('ai_pa_api_url', 'https://api.novita.ai/v3/openai');
    $theme_color = get_option('ai_pa_theme_color', '#10b981');
    ?>
    <div class="wrap">
        <h1>🚀 AI Portfolio Analyzer Pro Settings</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row">API Base URL</th>
                    <td>
                        <input type="url" name="ai_pa_api_url" value="<?php echo esc_attr($api_url); ?>" class="regular-text" />
                        <p class="description">Novita AI API base URL (default: https://api.novita.ai/v3/openai)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">API Key</th>
                    <td>
                        <input type="password" name="ai_pa_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" />
                        <p class="description">Your Novita AI API key for Meta Llama model</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">AI Model</th>
                    <td>
                        <input type="text" value="meta-llama/llama-3.1-8b-instruct" class="regular-text" readonly />
                        <p class="description">Currently using Meta Llama 3.1 8B Instruct model via Novita AI</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Theme Color</th>
                    <td>
                        <input type="color" name="ai_pa_theme_color" value="<?php echo esc_attr($theme_color); ?>" />
                        <p class="description">Choose your chatbot theme color</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Save Settings'); ?>
        </form>
        
        <div style="margin-top: 30px; padding: 20px; background: #f9f9f9; border-radius: 8px;">
            <h3>📋 Usage Instructions</h3>
            <p>Use the shortcode <code>[ai_portfolio_analyzer]</code> on any page or post to display the portfolio analyzer.</p>
            <h4>Features:</h4>
            <ul>
                <li>✅ Real-time PSX portfolio analysis</li>
                <li>✅ Interactive chat interface like ChatGPT</li>
                <li>✅ Portfolio visualization charts</li>
                <li>✅ Quick analysis templates</li>
                <li>✅ Export analysis reports</li>
                <li>✅ Mobile responsive design</li>
                <li>✅ Full-screen modern interface</li>
                <li>✅ Persistent chat history</li>
            </ul>
        </div>
    </div>
    <?php
}

// ----------- SHORTCODE FRONTEND -----------
add_shortcode('ai_portfolio_analyzer', function ($atts) {
    $atts = shortcode_atts([
        'height' => '100vh',
        'theme' => get_option('ai_pa_theme_color', '#10b981')
    ], $atts);
    
    ob_start();
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Reset and Base Styles */
        .ai-portfolio-app * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .ai-portfolio-app {
            --primary-color: #007aff;
            --primary-dark: #005bb5;
            --primary-light: #66b2ff;
            --bg-primary: #ffffff;
            --bg-secondary: #f7f7f7;
            --bg-tertiary: #e5e5ea;
            --text-primary: #000000;
            --text-secondary: #3c3c43;
            --text-muted: #8e8e93;
            --border-light: #d1d1d6;
            --border-medium: #c7c7cc;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1);
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            --radius-2xl: 1.5rem;
            --font-mono: 'SF Mono', 'Monaco', 'Inconsolata', 'Roboto Mono', monospace;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: var(--bg-secondary);
            z-index: 999999;
            overflow: hidden;
        }

        /* Dark Mode Support */
        .ai-portfolio-app.dark {
            --bg-primary: #1f2937;
            --bg-secondary: #111827;
            --bg-tertiary: #0f172a;
            --text-primary: #f9fafb;
            --text-secondary: #d1d5db;
            --text-muted: #9ca3af;
            --border-light: #374151;
            --border-medium: #4b5563;
        }

        /* Main Layout */
        .ai-chat-container {
            display: grid;
            grid-template-columns: 280px 1fr;
            height: 100vh;
            background: var(--bg-primary);
        }

        /* Sidebar */
        .ai-chat-sidebar {
            background: var(--bg-secondary);
            border-right: 1px solid var(--border-light);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-light);
            background: var(--bg-primary);
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--text-primary);
        }

        .brand-icon {
            width: 2rem;
            height: 2rem;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
        }

        .new-chat-btn {
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--radius-lg);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: center;
        }

        .new-chat-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        /* Chat History */
        .chat-history {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
        }

        .chat-history::-webkit-scrollbar {
            width: 4px;
        }

        .chat-history::-webkit-scrollbar-track {
            background: transparent;
        }

        .chat-history::-webkit-scrollbar-thumb {
            background: var(--border-medium);
            border-radius: 2px;
        }

        .history-section {
            margin-bottom: 1.5rem;
        }

        .history-title {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
            letter-spacing: 0.05em;
        }

        .history-item {
            padding: 0.75rem;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
            color: var(--text-secondary);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            position: relative;
        }

        .history-item:hover {
            background: var(--bg-tertiary);
            color: var(--text-primary);
        }

        .history-item.active {
            background: var(--primary-color);
            color: white;
        }

        .history-item .delete-chat {
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.2s;
            padding: 0.25rem;
            border-radius: 0.25rem;
        }

        .history-item:hover .delete-chat {
            opacity: 1;
        }

        .history-item .delete-chat:hover {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        /* Templates Section */
        .templates-section {
            border-top: 1px solid var(--border-light);
            padding: 1rem;
        }

        .template-grid {
            display: grid;
            gap: 0.5rem;
        }

        .template-btn {
            padding: 0.75rem;
            background: var(--bg-primary);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-md);
            text-align: left;
            font-size: 0.875rem;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
        }

        .template-btn:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            transform: translateX(2px);
        }

        /* Main Chat Area */
        .ai-chat-main {
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: relative;
        }

        /* Header */
        .chat-header {
            padding: 1rem 2rem;
            border-bottom: 1px solid var(--border-light);
            background: var(--bg-primary);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chat-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-indicator {
            width: 8px;
            height: 8px;
            background: var(--primary-color);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .header-actions {
            display: flex;
            gap: 0.5rem;
        }

        .header-btn {
            padding: 0.5rem;
            background: transparent;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-md);
            cursor: pointer;
            color: var(--text-secondary);
            transition: all 0.2s;
        }

        .header-btn:hover {
            background: var(--bg-tertiary);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        /* Messages Area */
        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 2rem;
            scroll-behavior: smooth;
            padding-bottom: 120px; /* Space for input area */
        }

        .messages-container::-webkit-scrollbar {
            width: 6px;
        }

        .messages-container::-webkit-scrollbar-track {
            background: var(--bg-secondary);
        }

        .messages-container::-webkit-scrollbar-thumb {
            background: var(--border-medium);
            border-radius: 3px;
        }

        /* Welcome Screen */
        .welcome-screen {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
            padding: 4rem 2rem;
        }

        .welcome-icon {
            font-size: 4rem;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .welcome-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--text-primary), var(--text-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .welcome-subtitle {
            font-size: 1.25rem;
            color: var(--text-secondary);
            margin-bottom: 3rem;
            line-height: 1.6;
        }

        .welcome-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 3rem;
        }

        .feature-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            text-align: left;
            transition: all 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-color);
        }

        .feature-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .feature-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .feature-desc {
            color: var(--text-secondary);
            font-size: 0.875rem;
            line-height: 1.5;
        }

        /* Message Bubbles */
        .message {
            margin-bottom: 2rem;
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .message-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .message-avatar {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .user-avatar {
            background: var(--primary-color);
            color: white;
        }

        .ai-avatar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .message-sender {
            font-weight: 600;
            color: var(--text-primary);
        }

        .message-time {
            color: var(--text-muted);
            font-size: 0.75rem;
        }

        .message-body {
            background: var(--bg-primary);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            color: var(--text-primary);
            line-height: 1.7;
            position: relative;
            word-wrap: break-word;
        }

        .message.user .message-body {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .message.ai .message-body {
            background: var(--bg-primary);
        }

        /* Typing Indicator */
        .typing-indicator {
            display: none;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 0;
            max-width: 800px;
            margin: 0 auto;
        }

        .typing-indicator.active {
            display: flex;
        }

        .typing-dots {
            display: flex;
            gap: 0.25rem;
        }

        .typing-dot {
            width: 8px;
            height: 8px;
            background: var(--primary-color);
            border-radius: 50%;
            animation: typingBounce 1.4s infinite ease-in-out;
        }

        .typing-dot:nth-child(1) { animation-delay: -0.32s; }
        .typing-dot:nth-child(2) { animation-delay: -0.16s; }

        @keyframes typingBounce {
            0%, 80%, 100% {
                transform: scale(0.8);
                opacity: 0.5;
            }
            40% {
                transform: scale(1.2);
                opacity: 1;
            }
        }

        /* Input Area */
        .input-area {
            position: fixed;
            bottom: 0;
            left: 280px;
            right: 0;
            padding: 1.5rem 2rem 2rem;
            background: var(--bg-primary);
            border-top: 1px solid var(--border-light);
            z-index: 1000;
        }

        .input-container {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }

        .input-wrapper {
            background: var(--bg-secondary);
            border: 2px solid var(--border-light);
            border-radius: var(--radius-2xl);
            padding: 0.75rem;
            transition: all 0.2s;
            display: flex;
            align-items: flex-end;
            gap: 0.75rem;
        }

        .input-wrapper:focus-within {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        .message-input {
            flex: 1;
            border: none;
            background: transparent;
            resize: none;
            outline: none;
            font-family: inherit;
            font-size: 1rem;
            line-height: 1.5;
            color: var(--text-primary);
            min-height: 24px;
            max-height: 200px;
            overflow-y: auto;
        }

        .message-input::placeholder {
            color: var(--text-muted);
        }

        .input-actions {
            display: flex;
            gap: 0.5rem;
            align-items: flex-end;
        }

        .send-btn {
            width: 2.5rem;
            height: 2.5rem;
            background: var(--primary-color);
            border: none;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            font-size: 1rem;
        }

        .send-btn:hover:not(:disabled) {
            background: var(--primary-dark);
            transform: scale(1.05);
        }

        .send-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .attachment-btn, .voice-btn {
            width: 2rem;
            height: 2rem;
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .attachment-btn:hover, .voice-btn:hover {
            background: var(--bg-tertiary);
            color: var(--primary-color);
        }

        /* Stock Inputs */
        .stock-inputs {
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .stock-inputs input {
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid var(--border-medium);
            background: var(--bg-tertiary);
            color: var(--text-primary);
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .stock-inputs input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        /* Command Suggestions */
        .command-suggestions {
            position: absolute;
            bottom: 100%;
            left: 0;
            right: 0;
            background: var(--bg-primary);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            max-height: 200px;
            overflow-y: auto;
            display: none;
            z-index: 1000;
        }

        .command-suggestions.active {
            display: block;
        }

        .command-item {
            padding: 0.75rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid var(--border-light);
            transition: all 0.2s;
        }

        .command-item:last-child {
            border-bottom: none;
        }

        .command-item:hover, .command-item.selected {
            background: var(--primary-color);
            color: white;
        }

        .command-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .command-desc {
            font-size: 0.875rem;
            opacity: 0.8;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .ai-chat-container {
                grid-template-columns: 1fr;
            }

            .ai-chat-sidebar {
                position: fixed;
                left: -280px;
                top: 0;
                width: 280px;
                height: 100vh;
                z-index: 1000;
                transition: left 0.3s ease;
                box-shadow: var(--shadow-xl);
            }

            .ai-chat-sidebar.open {
                left: 0;
            }

            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
                display: none;
            }

            .sidebar-overlay.active {
                display: block;
            }

            .input-area {
                left: 0;
                padding: 1rem;
            }

            .chat-header {
                padding: 1rem;
            }

            .messages-container {
                padding: 1rem;
            }

            .welcome-screen {
                padding: 2rem 1rem;
            }

            .welcome-title {
                font-size: 2rem;
            }

            .welcome-features {
                grid-template-columns: 1fr;
            }

            .stock-inputs {
                padding: 1rem;
            }
        }

        /* Message actions */
        .message-actions {
            margin-top: 0.75rem;
            display: flex;
            gap: 0.5rem;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .message:hover .message-actions {
            opacity: 1;
        }

        .action-btn {
            padding: 0.25rem 0.5rem;
            background: transparent;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
        }

        .action-btn:hover {
            background: var(--bg-tertiary);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        /* Focus styles */
        .template-btn:focus,
        .send-btn:focus,
        .header-btn:focus,
        .new-chat-btn:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }

        .message-input:focus {
            outline: none;
            color: var(--primary-color);
        }

        /* Code blocks in messages */
        .message-body pre {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-md);
            padding: 1rem;
            overflow-x: auto;
            font-family: var(--font-mono);
            font-size: 0.875rem;
            margin: 1rem 0;
        }

        .message-body code {
            background: var(--bg-tertiary);
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-sm);
            font-family: var(--font-mono);
            font-size: 0.875rem;
        }

        /* Loading States */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        /* Success/Error States */
        .success {
            border-color: var(--primary-color) !important;
        }

        .error {
            border-color: #ef4444 !important;
        }

        /* Accessibility */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>

    <div class="ai-portfolio-app">
        <div class="ai-chat-container">
            <!-- Sidebar -->
            <div class="ai-chat-sidebar" id="sidebar">
                <div class="sidebar-header">
                    <div class="sidebar-brand">
                        <div class="brand-icon">📈</div>
                        <span>Portfolio AI</span>
                    </div>
                    <button class="new-chat-btn" id="newChatBtn">
                        <span>➕</span>
                        New Analysis
                    </button>
                </div>

                <div class="chat-history" id="chatHistory">
                    <!-- Chat history will be dynamically populated -->
                </div>

                <div class="templates-section">
                    <div class="history-title">Quick Commands</div>
                    <div class="template-grid">
                        <button class="template-btn" data-command="/analyze">📊 Portfolio Analysis</button>
                        <button class="template-btn" data-command="/stocks">🏢 Stock Recommendations</button>
                        <button class="template-btn" data-command="/risk">⚠️ Risk Assessment</button>
                        <button class="template-btn" data-command="/trends">📈 Market Trends</button>
                        <button class="template-btn" data-command="/diversify">🎯 Diversification Tips</button>
                        <button class="template-btn" data-command="/sectors">🏭 Sector Analysis</button>
                    </div>
                </div>
            </div>

            <!-- Main Chat Area -->
            <div class="ai-chat-main">
                <div class="chat-header">
                    <div class="chat-title">
                        <button class="header-btn" id="sidebarToggle">☰</button>
                        <span>PSX Portfolio Analyzer</span>
                        <div class="status-indicator"></div>
                    </div>
                    <div class="header-actions">
                        <button class="header-btn" id="themeToggleBtn" title="Toggle Theme">🌙</button>
                        <button class="header-btn" id="exportBtn" title="Export Analysis">📄</button>
                        <button class="header-btn" id="settingsBtn" title="Settings">⚙️</button>
                        <button class="header-btn" id="fullscreenBtn" title="Fullscreen">⛶</button>
                    </div>
                </div>

                <div class="messages-container" id="messagesContainer">
                    <div class="stock-inputs" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; align-items: center;">
                        <input type="text" id="stockName" placeholder="Stock Name (e.g., HUBC)">
                        <input type="text" id="exchange" placeholder="Exchange (e.g., PSX)" value="Pakistan Stock Exchange">
                        <input type="text" id="chartType" placeholder="Chart Type (e.g., Daily)" value=" ">
                        <input type="date" id="date">
                    </div>
                    
                    <div class="welcome-screen" id="welcomeScreen">
                        <div class="welcome-icon">🚀</div>
                        <h1 class="welcome-title">PSX Portfolio Analyzer</h1>
                        <p class="welcome-subtitle">Your AI-powered assistant for Pakistan Stock Exchange analysis, portfolio optimization, and market insights</p>
                        
                        <div class="welcome-features">
                            <div class="feature-card">
                                <div class="feature-icon">📊</div>
                                <div class="feature-title">Portfolio Analysis</div>
                                <div class="feature-desc">Get comprehensive analysis of your PSX portfolio with performance metrics and insights</div>
                            </div>
                            <div class="feature-card">
                                <div class="feature-icon">🎯</div>
                                <div class="feature-title">Stock Recommendations</div>
                                <div class="feature-desc">Receive personalized stock picks based on market trends and your risk profile</div>
                            </div>
                            <div class="feature-card">
                                <div class="feature-icon">⚡</div>
                                <div class="feature-title">Real-time Insights</div>
                                <div class="feature-desc">Access live market data and instant analysis powered by advanced AI</div>
                            </div>
                            <div class="feature-card">
                                <div class="feature-icon">🛡️</div>
                                <div class="feature-title">Risk Management</div>
                                <div class="feature-desc">Understand and manage your investment risks with detailed assessments</div>
                            </div>
                        </div>
                    </div>

                    <div class="typing-indicator" id="typingIndicator">
                        <div class="message-avatar ai-avatar">AI</div>
                        <span>AI is analyzing your request</span>
                        <div class="typing-dots">
                            <div class="typing-dot"></div>
                            <div class="typing-dot"></div>
                            <div class="typing-dot"></div>
                        </div>
                    </div>
                </div>

                <div class="input-area">
                    <div class="input-container">
                        <div class="command-suggestions" id="commandSuggestions">
                            <div class="command-item" data-command="/analyze">
                                <div class="command-name">/analyze</div>
                                <div class="command-desc">Analyze your portfolio performance</div>
                            </div>
                            <div class="command-item" data-command="/stocks">
                                <div class="command-name">/stocks</div>
                                <div class="command-desc">Get stock recommendations</div>
                            </div>
                            <div class="command-item" data-command="/risk">
                                <div class="command-name">/risk</div>
                                <div class="command-desc">Assess portfolio risks</div>
                            </div>
                            <div class="command-item" data-command="/trends">
                                <div class="command-name">/trends</div>
                                <div class="command-desc">View market trends</div>
                            </div>
                            <div class="command-item" data-command="/diversify">
                                <div class="command-name">/diversify</div>
                                <div class="command-desc">Get diversification advice</div>
                            </div>
                            <div class="command-item" data-command="/sectors">
                                <div class="command-name">/sectors</div>
                                <div class="command-desc">Analyze different sectors</div>
                            </div>
                        </div>
                        <div class="input-wrapper">
                            <textarea 
                                class="message-input" 
                                id="messageInput" 
                                placeholder="Ask about your PSX portfolio, stocks, or market analysis... (Use / for commands)"
                                rows="1"></textarea>
                            <div class="input-actions">
                                <input type="file" id="fileInput" style="display: none;" accept="image/*,application/pdf,.txt,.csv,.json">
                                <button class="attachment-btn" id="attachmentBtn" title="Attach file">📎</button>
                                <button class="voice-btn" id="voiceBtn" title="Voice input">🎤</button>
                                <button class="send-btn" id="sendBtn" title="Send message">➤</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Overlay for Mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the Portfolio AI Chat Interface
            class PortfolioAI {
                constructor() {
                    this.initializeElements();
                    this.bindEvents();
                    this.chats = this.loadChats();
                    this.activeChatId = null;
                    this.sessionStats = {
                        queries: 0,
                        responseTimes: [],
                        sessionStart: Date.now()
                    };
                    this.commands = {
                        '/analyze': 'Analyze my PSX portfolio performance and provide detailed insights',
                        '/stocks': 'Recommend top PSX stocks based on current market conditions',
                        '/risk': 'Assess the risk profile of my current portfolio holdings',
                        '/trends': 'Show me the latest PSX market trends and analysis',
                        '/diversify': 'Provide portfolio diversification recommendations',
                        '/sectors': 'Compare and analyze different PSX sectors'
                    };
                    this.selectedCommandIndex = -1;
                    this.renderChatHistory();
                    this.startNewChat();
                    this.applyInitialTheme();
                    
                    // Set today's date as default
                    const today = new Date().toISOString().split('T')[0];
                    document.getElementById('date').value = today;
                }

                initializeElements() {
                    this.appContainer = document.querySelector('.ai-portfolio-app');
                    this.messageInput = document.getElementById('messageInput');
                    this.sendBtn = document.getElementById('sendBtn');
                    this.messagesContainer = document.getElementById('messagesContainer');
                    this.welcomeScreen = document.getElementById('welcomeScreen');
                    this.typingIndicator = document.getElementById('typingIndicator');
                    this.sidebarToggle = document.getElementById('sidebarToggle');
                    this.sidebar = document.getElementById('sidebar');
                    this.sidebarOverlay = document.getElementById('sidebarOverlay');
                    this.commandSuggestions = document.getElementById('commandSuggestions');
                    this.newChatBtn = document.getElementById('newChatBtn');
                    this.exportBtn = document.getElementById('exportBtn');
                    this.fullscreenBtn = document.getElementById('fullscreenBtn');
                    this.themeToggleBtn = document.getElementById('themeToggleBtn');
                    this.attachmentBtn = document.getElementById('attachmentBtn');
                    this.fileInput = document.getElementById('fileInput');
                    this.chatHistory = document.getElementById('chatHistory');
                }

                bindEvents() {
                    // Send message events - Fixed multiple event handling
                    this.sendBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        this.sendMessage();
                    });
                    
                    this.messageInput.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' && !e.shiftKey) {
                            e.preventDefault();
                            this.sendMessage();
                        }
                    });

                    // Auto-resize textarea
                    this.messageInput.addEventListener('input', () => {
                        this.autoResizeTextarea();
                        this.handleCommandSuggestions();
                    });

                    // Command suggestions navigation
                    this.messageInput.addEventListener('keydown', (e) => {
                        this.handleCommandNavigation(e);
                    });

                    // Template buttons
                    document.querySelectorAll('.template-btn').forEach(btn => {
                        btn.addEventListener('click', () => {
                            const command = btn.dataset.command;
                            this.messageInput.value = this.commands[command] || command;
                            this.messageInput.focus();
                            this.autoResizeTextarea();
                        });
                    });

                    // Command suggestion clicks
                    document.querySelectorAll('.command-item').forEach(item => {
                        item.addEventListener('click', () => {
                            const command = item.dataset.command;
                            this.messageInput.value = this.commands[command];
                            this.hideCommandSuggestions();
                            this.messageInput.focus();
                            this.autoResizeTextarea();
                        });
                    });

                    // Sidebar toggle
                    this.sidebarToggle.addEventListener('click', () => this.toggleSidebar());
                    this.sidebarOverlay.addEventListener('click', () => this.closeSidebar());

                    // New chat
                    this.newChatBtn.addEventListener('click', () => this.startNewChat());

                    // Export functionality
                    this.exportBtn.addEventListener('click', () => this.exportAnalysis());

                    // Fullscreen toggle
                    this.fullscreenBtn.addEventListener('click', () => this.toggleFullscreen());

                    // Theme toggle
                    this.themeToggleBtn.addEventListener('click', () => this.toggleTheme());

                    // Attachment button
                    this.attachmentBtn.addEventListener('click', () => this.fileInput.click());
                    this.fileInput.addEventListener('change', (e) => this.handleFileUpload(e));

                    // Close suggestions when clicking outside
                    document.addEventListener('click', (e) => {
                        if (!this.commandSuggestions.contains(e.target) && e.target !== this.messageInput) {
                            this.hideCommandSuggestions();
                        }
                    });

                    // Handle fullscreen changes
                    document.addEventListener('fullscreenchange', () => {
                        this.fullscreenBtn.innerHTML = document.fullscreenElement ? '🗗' : '⛶';
                    });
                }

                async sendMessage(message = null, fileData = null) {
                    const stockName = document.getElementById('stockName').value;
                    const exchange = document.getElementById('exchange').value;
                    const chartType = document.getElementById('chartType').value;
                    const date = document.getElementById('date').value;

                    let textMessage = message || this.messageInput.value.trim();
                    if (!textMessage && !fileData) return;

                    // Disable send button to prevent multiple sends
                    this.sendBtn.disabled = true;

                    // Replace placeholders with actual values
                    if (stockName) {
                        textMessage = textMessage.replace(/\[STOCK\]/g, stockName);
                    }
                    if (exchange) {
                        textMessage = textMessage.replace(/\[EXCHANGE\]/g, exchange);
                    }
                    if (chartType) {
                        textMessage = textMessage.replace(/\[CHARTTYPE\]/g, chartType);
                    }
                    if (date) {
                        textMessage = textMessage.replace(/\[DATE\]/g, date);
                    }

                    // Hide welcome screen
                    if (this.welcomeScreen) {
                        this.welcomeScreen.style.display = 'none';
                    }

                    // Add user message
                    this.addMessage(textMessage, 'user');
                    this.messageInput.value = '';
                    this.autoResizeTextarea();
                    this.hideCommandSuggestions();

                    // Show typing indicator
                    this.showTyping(true);

                    const startTime = Date.now();

                    try {
                        const body = { message: textMessage };
                        if (fileData) {
                            body.file = fileData;
                        }

                        const response = await fetch('<?php echo esc_url(rest_url('ai-pa/v1/analyze')); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                            },
                            body: JSON.stringify(body)
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }

                        const data = await response.json();
                        const endTime = Date.now();

                        // Update stats
                        this.sessionStats.queries++;
                        this.sessionStats.responseTimes.push(endTime - startTime);

                        // Add AI response
                        this.showTyping(false);
                        this.addMessage(data.reply || 'Sorry, I encountered an error. Please try again.', 'ai');

                    } catch (error) {
                        this.showTyping(false);
                        this.addMessage('Sorry, I encountered a connection error. Please check your internet and try again.', 'ai');
                        console.error('Error:', error);
                    } finally {
                        this.sendBtn.disabled = false;
                        this.saveChats();
                        this.renderChatHistory();
                    }
                }

                addMessage(content, type, save = true) {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = `message ${type}`;
                    
                    const now = new Date();
                    const timeString = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

                    messageDiv.innerHTML = `
                        <div class="message-content">
                            <div class="message-header">
                                <div class="message-avatar ${type}-avatar">
                                    ${type === 'user' ? 'You' : 'AI'}
                                </div>
                                <span class="message-sender">${type === 'user' ? 'You' : 'Portfolio AI'}</span>
                                <span class="message-time">${timeString}</span>
                            </div>
                            <div class="message-body">
                                ${this.formatMessage(content)}
                            </div>
                            <div class="message-actions">
                                <button class="action-btn copy-btn">📋 Copy</button>
                                ${type === 'ai' ? '<button class="action-btn regenerate-btn">🔄 Regenerate</button>' : ''}
                                <button class="action-btn share-btn">🔗 Share</button>
                            </div>
                        </div>
                    `;

                    // Insert before typing indicator if it exists, otherwise append
                    if (this.typingIndicator && this.typingIndicator.parentNode === this.messagesContainer) {
                        this.messagesContainer.insertBefore(messageDiv, this.typingIndicator);
                    } else {
                        this.messagesContainer.appendChild(messageDiv);
                    }
                    
                    this.scrollToBottom();

                    // Bind action button events
                    this.bindMessageActions(messageDiv);

                    if (save && this.activeChatId) {
                        const chat = this.chats[this.activeChatId];
                        if (chat) {
                            chat.messages.push({ content, type, timestamp: Date.now() });
                            if (chat.messages.length === 1 && type === 'user') {
                                chat.title = content.substring(0, 30) + (content.length > 30 ? '...' : '');
                            }
                            chat.timestamp = Date.now();
                        }
                    }
                }

                formatMessage(content) {
                    // Basic markdown-like formatting
                    return content
                        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                        .replace(/\*(.*?)\*/g, '<em>$1</em>')
                        .replace(/`(.*?)`/g, '<code>$1</code>')
                        .replace(/\n/g, '<br>');
                }

                bindMessageActions(messageDiv) {
                    const copyBtn = messageDiv.querySelector('.copy-btn');
                    const regenerateBtn = messageDiv.querySelector('.regenerate-btn');
                    const shareBtn = messageDiv.querySelector('.share-btn');

                    if (copyBtn) {
                        copyBtn.addEventListener('click', () => {
                            const messageText = messageDiv.querySelector('.message-body').textContent;
                            navigator.clipboard.writeText(messageText).then(() => {
                                copyBtn.textContent = '✅ Copied';
                                setTimeout(() => copyBtn.textContent = '📋 Copy', 2000);
                            });
                        });
                    }

                    if (regenerateBtn) {
                        regenerateBtn.addEventListener('click', () => {
                            this.regenerateLastResponse();
                        });
                    }

                    if (shareBtn) {
                        shareBtn.addEventListener('click', () => {
                            this.shareMessage(messageDiv);
                        });
                    }
                }

                showTyping(show) {
                    this.typingIndicator.classList.toggle('active', show);
                    if (show) {
                        this.scrollToBottom();
                    }
                }

                scrollToBottom() {
                    setTimeout(() => {
                        this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
                    }, 100);
                }

                autoResizeTextarea() {
                    this.messageInput.style.height = 'auto';
                    this.messageInput.style.height = Math.min(this.messageInput.scrollHeight, 200) + 'px';
                }

                handleCommandSuggestions() {
                    const value = this.messageInput.value;
                    if (value.startsWith('/')) {
                        const command = value.toLowerCase();
                        const matchingCommands = Object.keys(this.commands).filter(cmd => 
                            cmd.toLowerCase().startsWith(command)
                        );
                        
                        if (matchingCommands.length > 0 && value !== '/') {
                            this.showCommandSuggestions(matchingCommands);
                        } else {
                            this.hideCommandSuggestions();
                        }
                    } else {
                        this.hideCommandSuggestions();
                    }
                }

                showCommandSuggestions(commands) {
                    const items = this.commandSuggestions.querySelectorAll('.command-item');
                    items.forEach(item => item.style.display = 'none');
                    
                    commands.forEach(command => {
                        const item = this.commandSuggestions.querySelector(`[data-command="${command}"]`);
                        if (item) item.style.display = 'block';
                    });
                    
                    this.commandSuggestions.classList.add('active');
                    this.selectedCommandIndex = -1;
                }

                hideCommandSuggestions() {
                    this.commandSuggestions.classList.remove('active');
                    this.selectedCommandIndex = -1;
                }

                handleCommandNavigation(e) {
                    if (!this.commandSuggestions.classList.contains('active')) return;

                    const visibleItems = Array.from(this.commandSuggestions.querySelectorAll('.command-item'))
                        .filter(item => item.style.display !== 'none');

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        this.selectedCommandIndex = Math.min(this.selectedCommandIndex + 1, visibleItems.length - 1);
                        this.updateSelectedCommand(visibleItems);
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        this.selectedCommandIndex = Math.max(this.selectedCommandIndex - 1, -1);
                        this.updateSelectedCommand(visibleItems);
                    } else if (e.key === 'Enter' && this.selectedCommandIndex >= 0) {
                        e.preventDefault();
                        const selectedItem = visibleItems[this.selectedCommandIndex];
                        const command = selectedItem.dataset.command;
                        this.messageInput.value = this.commands[command];
                        this.hideCommandSuggestions();
                        this.autoResizeTextarea();
                    } else if (e.key === 'Escape') {
                        this.hideCommandSuggestions();
                    }
                }

                updateSelectedCommand(visibleItems) {
                    visibleItems.forEach((item, index) => {
                        item.classList.toggle('selected', index === this.selectedCommandIndex);
                    });
                }

                toggleSidebar() {
                    if (window.innerWidth <= 768) {
                        this.sidebar.classList.toggle('open');
                        this.sidebarOverlay.classList.toggle('active');
                    }
                }

                closeSidebar() {
                    this.sidebar.classList.remove('open');
                    this.sidebarOverlay.classList.remove('active');
                }

                startNewChat() {
                    this.activeChatId = `chat_${Date.now()}`;
                    this.chats[this.activeChatId] = {
                        id: this.activeChatId,
                        title: 'New Chat',
                        messages: [],
                        timestamp: Date.now()
                    };
                    this.loadChat(this.activeChatId);
                    this.saveChats();
                }

                loadChat(chatId) {
                    if (!this.chats[chatId]) return;

                    this.activeChatId = chatId;
                    const chat = this.chats[chatId];
                    
                    // Clear messages
                    const messages = this.messagesContainer.querySelectorAll('.message');
                    messages.forEach(msg => msg.remove());

                    if (chat.messages.length === 0) {
                        this.welcomeScreen.style.display = 'block';
                    } else {
                        this.welcomeScreen.style.display = 'none';
                        chat.messages.forEach(message => {
                            this.addMessage(message.content, message.type, false);
                        });
                    }

                    this.renderChatHistory();
                }

                deleteChat(chatId, event) {
                    event.stopPropagation();
                    if (confirm('Are you sure you want to delete this chat?')) {
                        delete this.chats[chatId];
                        this.saveChats();
                        
                        if (this.activeChatId === chatId) {
                            this.startNewChat();
                        } else {
                            this.renderChatHistory();
                        }
                    }
                }

                exportAnalysis() {
                    if (!this.activeChatId || !this.chats[this.activeChatId] || this.chats[this.activeChatId].messages.length === 0) {
                        alert('No conversation to export yet. Start chatting with the AI first!');
                        return;
                    }

                    const chat = this.chats[this.activeChatId];
                    const exportData = {
                        sessionDate: new Date().toISOString().split('T')[0],
                        chatTitle: chat.title,
                        totalQueries: this.sessionStats.queries,
                        averageResponseTime: this.sessionStats.responseTimes.length > 0 ? 
                            (this.sessionStats.responseTimes.reduce((a, b) => a + b) / this.sessionStats.responseTimes.length / 1000).toFixed(2) + 's' : 'N/A',
                        messages: chat.messages
                    };

                    const dataStr = JSON.stringify(exportData, null, 2);
                    const dataBlob = new Blob([dataStr], {type: 'application/json'});
                    const url = URL.createObjectURL(dataBlob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = `PSX_Portfolio_Analysis_${new Date().toISOString().split('T')[0]}.json`;
                    link.click();
                    URL.revokeObjectURL(url);
                }

                toggleFullscreen() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen();
                    } else {
                        document.exitFullscreen();
                    }
                }

                regenerateLastResponse() {
                    const chat = this.chats[this.activeChatId];
                    if (!chat || chat.messages.length < 2) return;

                    // Find the last user message
                    const lastUserMessageIndex = chat.messages.findLastIndex(msg => msg.type === 'user');
                    if (lastUserMessageIndex === -1) return;

                    const lastUserMessage = chat.messages[lastUserMessageIndex];
                    
                    // Remove the last AI response if it exists
                    const lastAiMessageIndex = chat.messages.findLastIndex(msg => msg.type === 'ai');
                    if (lastAiMessageIndex > lastUserMessageIndex) {
                        chat.messages.splice(lastAiMessageIndex, 1);
                    }

                    // Remove the AI message from DOM
                    const aiMessages = this.messagesContainer.querySelectorAll('.message.ai');
                    if (aiMessages.length > 0) {
                        const lastAiMessage = aiMessages[aiMessages.length - 1];
                        lastAiMessage.remove();
                    }
                    
                    // Resend the message
                    this.sendMessage(lastUserMessage.content);
                }

                shareMessage(messageDiv) {
                    const messageText = messageDiv.querySelector('.message-body').textContent;
                    const shareData = {
                        title: 'PSX Portfolio Analysis',
                        text: messageText,
                        url: window.location.href
                    };

                    if (navigator.share && navigator.canShare && navigator.canShare(shareData)) {
                        navigator.share(shareData);
                    } else {
                        navigator.clipboard.writeText(messageText).then(() => {
                            const shareBtn = messageDiv.querySelector('.share-btn');
                            shareBtn.textContent = '✅ Copied to clipboard';
                            setTimeout(() => shareBtn.textContent = '🔗 Share', 2000);
                        });
                    }
                }

                toggleTheme() {
                    const isDark = this.appContainer.classList.toggle('dark');
                    localStorage.setItem('ai_pa_theme', isDark ? 'dark' : 'light');
                    this.themeToggleBtn.textContent = isDark ? '☀️' : '🌙';
                }

                applyInitialTheme() {
                    const savedTheme = localStorage.getItem('ai_pa_theme');
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
                        this.appContainer.classList.add('dark');
                        this.themeToggleBtn.textContent = '☀️';
                    } else {
                        this.appContainer.classList.remove('dark');
                        this.themeToggleBtn.textContent = '🌙';
                    }
                }

                loadChats() {
                    try {
                        const chats = localStorage.getItem('ai_pa_chats');
                        return chats ? JSON.parse(chats) : {};
                    } catch (error) {
                        console.error('Error loading chats:', error);
                        return {};
                    }
                }

                saveChats() {
                    try {
                        localStorage.setItem('ai_pa_chats', JSON.stringify(this.chats));
                    } catch (error) {
                        console.error('Error saving chats:', error);
                    }
                }

                renderChatHistory() {
                    this.chatHistory.innerHTML = '';
                    const chats = Object.values(this.chats).sort((a, b) => b.timestamp - a.timestamp);
                    
                    if (chats.length === 0) {
                        this.chatHistory.innerHTML = '<div class="history-item">No recent chats</div>';
                        return;
                    }

                    const today = new Date().toDateString();
                    const yesterday = new Date(Date.now() - 86400000).toDateString();

                    let currentSection = '';
                    let currentContainer = null;

                    chats.forEach(chat => {
                        const chatDate = new Date(chat.timestamp).toDateString();
                        let sectionTitle = 'Older';
                        
                        if (chatDate === today) {
                            sectionTitle = 'Today';
                        } else if (chatDate === yesterday) {
                            sectionTitle = 'Yesterday';
                        }

                        if (sectionTitle !== currentSection) {
                            const sectionDiv = document.createElement('div');
                            sectionDiv.className = 'history-section';
                            sectionDiv.innerHTML = `<div class="history-title">${sectionTitle}</div>`;
                            this.chatHistory.appendChild(sectionDiv);
                            currentContainer = sectionDiv;
                            currentSection = sectionTitle;
                        }

                        const historyItem = document.createElement('div');
                        historyItem.className = `history-item ${chat.id === this.activeChatId ? 'active' : ''}`;
                        historyItem.innerHTML = `
                            <span class="chat-title-text" title="${chat.title}">${chat.title}</span>
                            <button class="delete-chat" title="Delete chat">×</button>
                        `;
                        
                        // Click to load chat
                        historyItem.addEventListener('click', (e) => {
                            if (e.target.classList.contains('delete-chat')) return;
                            this.loadChat(chat.id);
                        });

                        // Delete chat
                        const deleteBtn = historyItem.querySelector('.delete-chat');
                        deleteBtn.addEventListener('click', (e) => {
                            this.deleteChat(chat.id, e);
                        });

                        currentContainer.appendChild(historyItem);
                    });
                }

                handleFileUpload(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    if (file.size > 5 * 1024 * 1024) { // 5MB limit
                        alert('File size too large. Please select a file smaller than 5MB.');
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = (event) => {
                        const fileData = event.target.result;
                        this.sendMessage(`Attached file: ${file.name}`, fileData);
                    };
                    reader.readAsDataURL(file);
                    
                    // Reset file input
                    this.fileInput.value = '';
                }

                // Utility method to find last index (for older browsers)
                findLastIndex(array, predicate) {
                    for (let i = array.length - 1; i >= 0; i--) {
                        if (predicate(array[i])) {
                            return i;
                        }
                    }
                    return -1;
                }
            }

            // Initialize the Portfolio AI
            window.portfolioAI = new PortfolioAI();

            // Add welcome animations
            setTimeout(() => {
                const features = document.querySelectorAll('.feature-card');
                features.forEach((feature, index) => {
                    setTimeout(() => {
                        feature.style.opacity = '0';
                        feature.style.transform = 'translateY(20px)';
                        feature.style.animation = 'slideUp 0.6s ease-out forwards';
                    }, index * 100);
                });
            }, 500);

            // Handle browser back/forward buttons
            window.addEventListener('popstate', function() {
                // Handle navigation if needed
            });

            // Handle page visibility changes
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    // Page is hidden, pause any ongoing operations
                } else {
                    // Page is visible, resume operations
                }
            });

            // Handle online/offline status
            window.addEventListener('online', function() {
                console.log('Connection restored');
            });

            window.addEventListener('offline', function() {
                console.log('Connection lost');
            });
        });
    </script>
    <?php
    return ob_get_clean();
});

// ----------- REST API ENDPOINT -----------
add_action('rest_api_init', function () {
    register_rest_route('ai-pa/v1', '/analyze', [
        'methods' => 'POST',
        'callback' => 'ai_pa_analyze_portfolio',
        'permission_callback' => '__return_true',
        'args' => [
            'message' => [
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'file' => [
                'required' => false,
                'type' => 'string',
            ],
        ],
    ]);
});

function ai_pa_analyze_portfolio(WP_REST_Request $request) {
    // Get API configuration
    $api_key = get_option('ai_pa_api_key', '');
    $base_url = get_option('ai_pa_api_url', 'https://api.novita.ai/v3/openai');
    $model_name = 'meta-llama/llama-3.1-8b-instruct';

    // Fallback API key if not set in options
    if (empty($api_key)) {
        $api_key = 'sk_pkPfR10Ibpy3SfYj02ROX6Zpm_O8M7YiRfUfGRuvpQU';
    }

    $msg = sanitize_text_field($request->get_param('message'));
    $file_data = $request->get_param('file');

    // Enhanced logging
    error_log('AI Portfolio Analyzer Debug - API Key configured: ' . (!empty($api_key) ? 'Yes' : 'No'));
    error_log('AI Portfolio Analyzer Debug - Message received: ' . substr($msg, 0, 100) . (strlen($msg) > 100 ? '...' : ''));
    error_log('AI Portfolio Analyzer Debug - File attached: ' . (!empty($file_data) ? 'Yes' : 'No'));

    // Validation
    if (empty($api_key)) {
        return new WP_REST_Response([
            'reply' => '❌ Configuration Error: API key not configured. Please check plugin settings in WordPress Admin → Portfolio AI.'
        ], 400);
    }
    
    if (empty($msg)) {
        return new WP_REST_Response([
            'reply' => '❌ Error: Message is required.'
        ], 400);
    }

    // Prepare user content
    $user_content = $msg;
    if (!empty($file_data)) {
        $file_parts = explode(',', $file_data, 2);
        if (count($file_parts) === 2) {
            $file_type = $file_parts[0];
            $file_content = base64_decode($file_parts[1]);
            
            if (strpos($file_type, 'image') !== false) {
                $user_content .= "\n\n[User has attached an image. Please acknowledge the image and provide relevant analysis if possible.]";
            } elseif (strpos($file_type, 'text') !== false || strpos($file_type, 'csv') !== false) {
                $user_content .= "\n\n[User has attached a file with the following content:]\n" . substr($file_content, 0, 2000);
            } else {
                $user_content .= "\n\n[User has attached a file. Please acknowledge and provide relevant analysis.]";
            }
        }
    }

    // Prepare API request
    $body = [
        "model" => $model_name,
        "messages" => [
            [
                "role" => "system",
                "content" => "You are an expert Pakistani stock market analyst specializing in PSX (Pakistan Stock Exchange). Provide professional, actionable insights about stocks, portfolio management, and market analysis. Always use Pakistani Rupee (PKR) symbol ₨ for currency. Be concise, accurate, and helpful. When analyzing portfolios, consider risk management, diversification, and current market conditions in Pakistan."
            ],
            [
                "role" => "user", 
                "content" => $user_content
            ]
        ],
        "max_tokens" => 1500,
        "temperature" => 0.7,
        "stream" => false
    ];

    // Make API request
    $response = wp_remote_post("$base_url/chat/completions", [
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
            'User-Agent' => 'WordPress-AI-Portfolio-Analyzer/3.1'
        ],
        'body' => json_encode($body),
        'timeout' => 60,
        'sslverify' => true
    ]);

    // Handle request errors
    if (is_wp_error($response)) {
        $error_msg = $response->get_error_message();
        error_log('AI Portfolio Analyzer Debug - WP Error: ' . $error_msg);
        return new WP_REST_Response([
            'reply' => '❌ Connection Error: ' . $error_msg . '. Please check your internet connection and try again.'
        ], 500);
    }

    $http_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);
    
    error_log('AI Portfolio Analyzer Debug - HTTP Code: ' . $http_code);
    error_log('AI Portfolio Analyzer Debug - Response Length: ' . strlen($response_body));

    // Handle HTTP errors
    if ($http_code !== 200) {
        $error_detail = 'Unknown error';
        $data = json_decode($response_body, true);
        if (isset($data['error']['message'])) {
            $error_detail = $data['error']['message'];
        } elseif (isset($data['message'])) {
            $error_detail = $data['message'];
        }
        
        error_log('AI Portfolio Analyzer Debug - API Error: ' . $error_detail);
        
        return new WP_REST_Response([
            'reply' => "❌ API Error (Code: $http_code): $error_detail. Please check your API key configuration and try again."
        ], $http_code);
    }

    // Parse response
    $data = json_decode($response_body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('AI Portfolio Analyzer Debug - JSON Parse Error: ' . json_last_error_msg());
        return new WP_REST_Response([
            'reply' => '❌ Invalid response format from AI service. Please try again.'
        ], 500);
    }
    
    // Extract AI reply
    if (isset($data['choices'][0]['message']['content'])) {
        $ai_reply = trim($data['choices'][0]['message']['content']);
        
        // Basic response validation
        if (empty($ai_reply)) {
            return new WP_REST_Response([
                'reply' => '❌ Empty response from AI service. Please try rephrasing your question.'
            ], 500);
        }
        
        return new WP_REST_Response([
            'reply' => $ai_reply,
            'timestamp' => current_time('timestamp'),
            'model' => $model_name
        ]);
    }
    
    // Handle API errors in response
    if (isset($data['error'])) {
        error_log('AI Portfolio Analyzer Debug - API Response Error: ' . $data['error']['message']);
        return new WP_REST_Response([
            'reply' => '❌ AI Service Error: ' . $data['error']['message']
        ], 500);
    }
    
    // Fallback error
    error_log('AI Portfolio Analyzer Debug - Unexpected Response Format: ' . print_r($data, true));
    return new WP_REST_Response([
        'reply' => '❌ Unexpected response format from AI service. Please contact support if this issue persists.'
    ], 500);
}

// ----------- ACTIVATION HOOK -----------
register_activation_hook(__FILE__, 'ai_portfolio_analyzer_activate');

function ai_portfolio_analyzer_activate() {
    // Set default options
    add_option('ai_pa_api_url', 'https://api.novita.ai/v3/openai');
    add_option('ai_pa_theme_color', '#10b981');
    
    // Set default API key if not already set
    if (get_option('ai_pa_api_key') === false) {
        add_option('ai_pa_api_key', 'sk_pkPfR10Ibpy3SfYj02ROX6Zpm_O8M7YiRfUfGRuvpQU');
    }
    
    // Set activation flag
    set_transient('ai_pa_activated', true, 60);
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

// ----------- DEACTIVATION HOOK -----------
register_deactivation_hook(__FILE__, 'ai_portfolio_analyzer_deactivate');

function ai_portfolio_analyzer_deactivate() {
    // Clean up transients
    delete_transient('ai_pa_activated');
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

// ----------- ADMIN NOTICES -----------
add_action('admin_notices', function() {
    if (get_transient('ai_pa_activated')) {
        delete_transient('ai_pa_activated');
        ?>
        <div class="notice notice-success is-dismissible">
            <p><strong>✅ AI Portfolio Analyzer Pro</strong> activated successfully!</p>
            <p>Use shortcode <code>[ai_portfolio_analyzer]</code> on any page or post to display the analyzer.</p>
            <p><a href="<?php echo admin_url('admin.php?page=ai-portfolio-main'); ?>">Configure Settings</a></p>
        </div>
        <?php
    }
    
    // Check if API key is configured
    $api_key = get_option('ai_pa_api_key', '');
    if (empty($api_key) && isset($_GET['page']) && $_GET['page'] === 'ai-portfolio-main') {
        ?>
        <div class="notice notice-warning">
            <p><strong>⚠️ AI Portfolio Analyzer:</strong> Please configure your API key below for the plugin to work properly.</p>
        </div>
        <?php
    }
});

// ----------- AJAX HANDLERS FOR ADMIN -----------
add_action('wp_ajax_ai_pa_test_connection', 'ai_pa_test_connection');

function ai_pa_test_connection() {
    check_ajax_referer('ai_pa_nonce', 'nonce');
    
    $api_key = sanitize_text_field($_POST['api_key']);
    $api_url = sanitize_text_field($_POST['api_url']);
    
    if (empty($api_key) || empty($api_url)) {
        wp_die(json_encode(['success' => false, 'message' => 'API key and URL are required']));
    }
    
    // Test API connection
    $response = wp_remote_post("$api_url/chat/completions", [
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key
        ],
        'body' => json_encode([
            'model' => 'meta-llama/llama-3.1-8b-instruct',
            'messages' => [['role' => 'user', 'content' => 'Test connection']],
            'max_tokens' => 10
        ]),
        'timeout' => 30
    ]);
    
    if (is_wp_error($response)) {
        wp_die(json_encode(['success' => false, 'message' => $response->get_error_message()]));
    }
    
    $http_code = wp_remote_retrieve_response_code($response);
    if ($http_code === 200) {
        wp_die(json_encode(['success' => true, 'message' => 'Connection successful!']));
    } else {
        wp_die(json_encode(['success' => false, 'message' => 'Connection failed with code: ' . $http_code]));
    }
}

// ----------- SECURITY ENHANCEMENTS -----------
add_action('rest_api_init', function() {
    // Add rate limiting for API endpoint
    add_filter('rest_pre_dispatch', 'ai_pa_rate_limit', 10, 3);
});

function ai_pa_rate_limit($result, $server, $request) {
    if ($request->get_route() !== '/ai-pa/v1/analyze') {
        return $result;
    }
    
    $ip = $_SERVER['REMOTE_ADDR'];
    $transient_key = 'ai_pa_rate_limit_' . md5($ip);
    $requests = get_transient($transient_key);
    
    if ($requests === false) {
        set_transient($transient_key, 1, MINUTE_IN_SECONDS);
    } elseif ($requests >= 30) { // 30 requests per minute
        return new WP_Error('rate_limit_exceeded', 'Too many requests. Please try again later.', ['status' => 429]);
    } else {
        set_transient($transient_key, $requests + 1, MINUTE_IN_SECONDS);
    }
    
    return $result;
}

// ----------- PERFORMANCE OPTIMIZATIONS -----------
add_action('wp_enqueue_scripts', function() {
    if (has_shortcode(get_post()->post_content ?? '', 'ai_portfolio_analyzer')) {
        // Preload critical resources
        echo '<link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" as="style">';
        echo '<link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js" as="script">';
    }
});

// ----------- ERROR LOGGING -----------
if (!function_exists('ai_pa_log_error')) {
    function ai_pa_log_error($message, $context = []) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('AI Portfolio Analyzer: ' . $message . ' Context: ' . print_r($context, true));
        }
    }
}
?>
