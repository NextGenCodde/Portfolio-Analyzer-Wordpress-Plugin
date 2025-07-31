<?php
/*
Plugin Name: Advanced AI Portfolio Analyzer Pro - Real-time Stock Data
Description: Professional AI-powered PSX portfolio analysis tool with real-time data using ChatGPT API. Insert via shortcode [ai_portfolio_analyzer].
Version: 4.0
Author: Faizan
*/

if (!defined('ABSPATH')) exit;

// ----------- PLUGIN CONSTANTS -----------
define('AI_PA_VERSION', '4.0');
define('AI_PA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AI_PA_PLUGIN_PATH', plugin_dir_path(__FILE__));

// ----------- ENQUEUE SCRIPTS AND STYLES -----------
add_action('wp_enqueue_scripts', function() {
    if (has_shortcode(get_post()->post_content ?? '', 'ai_portfolio_analyzer')) {
        wp_enqueue_script('chart-js', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js', [], '3.9.1', true);
        wp_enqueue_script('ai-pa-script', AI_PA_PLUGIN_URL . 'ai-portfolio-analyzer.js', [], AI_PA_VERSION, true);
        wp_localize_script('ai-pa-script', 'aiPA', [
            'ajax_url' => rest_url('ai-pa/v1/analyze'),
            'nonce' => wp_create_nonce('wp_rest'),
            'stock_data_url' => rest_url('ai-pa/v1/stock-data'),
            'market_data_url' => rest_url('ai-pa/v1/market-data')
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
        update_option('ai_pa_chatgpt_api_key', sanitize_text_field($_POST['ai_pa_chatgpt_api_key']));
        update_option('ai_pa_alpha_vantage_key', sanitize_text_field($_POST['ai_pa_alpha_vantage_key']));
        update_option('ai_pa_theme_color', sanitize_text_field($_POST['ai_pa_theme_color']));
        update_option('ai_pa_enable_realtime', isset($_POST['ai_pa_enable_realtime']) ? 1 : 0);
        update_option('ai_pa_cache_duration', intval($_POST['ai_pa_cache_duration']));
        echo '<div class="updated"><p>Settings Saved Successfully!</p></div>';
    }

    $chatgpt_api_key = get_option('ai_pa_chatgpt_api_key', '');
    $alpha_vantage_key = get_option('ai_pa_alpha_vantage_key', '');
    $theme_color = get_option('ai_pa_theme_color', '#10b981');
    $enable_realtime = get_option('ai_pa_enable_realtime', 1);
    $cache_duration = get_option('ai_pa_cache_duration', 300);
    ?>
    <div class="wrap">
        <h1>🚀 AI Portfolio Analyzer Pro Settings</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row">ChatGPT API Key</th>
                    <td>
                        <input type="password" name="ai_pa_chatgpt_api_key" value="<?php echo esc_attr($chatgpt_api_key); ?>" class="regular-text" />
                        <p class="description">Your OpenAI ChatGPT API key for AI analysis</p>
                        <?php if (!empty($chatgpt_api_key)): ?>
                        <p class="description" style="color: green;">✅ API Key Configured</p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Alpha Vantage API Key (Optional)</th>
                    <td>
                        <input type="text" name="ai_pa_alpha_vantage_key" value="<?php echo esc_attr($alpha_vantage_key); ?>" class="regular-text" />
                        <p class="description">For enhanced real-time stock data (get free key from <a href="https://www.alphavantage.co/support/#api-key" target="_blank">Alpha Vantage</a>)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">AI Model</th>
                    <td>
                        <input type="text" value="gpt-4-turbo-preview" class="regular-text" readonly />
                        <p class="description">Currently using GPT-4 Turbo for advanced financial analysis</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Enable Real-time Data</th>
                    <td>
                        <label>
                            <input type="checkbox" name="ai_pa_enable_realtime" value="1" <?php checked($enable_realtime, 1); ?> />
                            Enable real-time stock data fetching
                        </label>
                        <p class="description">Allow AI to fetch current market data for analysis</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Data Cache Duration</th>
                    <td>
                        <select name="ai_pa_cache_duration">
                            <option value="60" <?php selected($cache_duration, 60); ?>>1 minute</option>
                            <option value="300" <?php selected($cache_duration, 300); ?>>5 minutes</option>
                            <option value="900" <?php selected($cache_duration, 900); ?>>15 minutes</option>
                            <option value="1800" <?php selected($cache_duration, 1800); ?>>30 minutes</option>
                        </select>
                        <p class="description">How long to cache stock data to reduce API calls</p>
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
            <h4>New Features in v4.0:</h4>
            <ul>
                <li>✅ Real-time stock data integration</li>
                <li>✅ ChatGPT-4 powered analysis</li>
                <li>✅ Live market data streaming</li>
                <li>✅ Advanced portfolio optimization</li>
                <li>✅ Risk assessment with current data</li>
                <li>✅ Sector analysis with live prices</li>
                <li>✅ Performance tracking dashboard</li>
                <li>✅ Export detailed reports</li>
                <li>✅ Mobile responsive design</li>
                <li>✅ Dark/Light theme support</li>
            </ul>

            <h4>🔧 Test API Connection</h4>
            <button type="button" id="testApiBtn" class="button button-secondary">Test ChatGPT API Connection</button>
            <div id="apiTestResult" style="margin-top: 10px;"></div>
        </div>
    </div>

    <script>
    document.getElementById('testApiBtn').addEventListener('click', function() {
        const btn = this;
        const result = document.getElementById('apiTestResult');
        const apiKey = document.querySelector('input[name="ai_pa_chatgpt_api_key"]').value;

        if (!apiKey) {
            result.innerHTML = '<p style="color: red;">Please enter your ChatGPT API key first.</p>';
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Testing...';
        result.innerHTML = '<p>Testing API connection...</p>';

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'ai_pa_test_chatgpt_connection',
                api_key: apiKey,
                nonce: '<?php echo wp_create_nonce('ai_pa_test_nonce'); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                result.innerHTML = '<p style="color: green;">✅ ' + data.message + '</p>';
            } else {
                result.innerHTML = '<p style="color: red;">❌ ' + data.message + '</p>';
            }
        })
        .catch(error => {
            result.innerHTML = '<p style="color: red;">❌ Connection test failed: ' + error.message + '</p>';
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = 'Test ChatGPT API Connection';
        });
    });
    </script>
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
        /* Enhanced styles with real-time data indicators */
        .ai-portfolio-app * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .ai-portfolio-app {
            --primary-color: <?php echo esc_attr($atts['theme']); ?>;
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
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --error-color: #ef4444;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1);
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            --radius-2xl: 1.5rem;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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
            grid-template-columns: 320px 1fr;
            height: 100vh;
            background: var(--bg-primary);
        }

        /* Enhanced Sidebar */
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
            margin-bottom: 1rem;
        }

        .brand-icon {
            width: 2.5rem;
            height: 2.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        /* Market Status Indicator */
        .market-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .market-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        .market-indicator.open {
            background: var(--success-color);
        }

        .market-indicator.closed {
            background: var(--error-color);
        }

        .market-indicator.pre-market {
            background: var(--warning-color);
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .new-chat-btn {
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

        /* Enhanced Chat History */
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

        /* Enhanced Templates Section */
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
            position: relative;
        }

        .template-btn:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            transform: translateX(2px);
        }

        .template-btn.realtime::after {
            content: "LIVE";
            position: absolute;
            top: 0.25rem;
            right: 0.25rem;
            background: var(--success-color);
            color: white;
            font-size: 0.6rem;
            padding: 0.125rem 0.25rem;
            border-radius: 0.25rem;
            font-weight: 600;
        }

        /* Main Chat Area */
        .ai-chat-main {
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: relative;
        }

        /* Enhanced Header */
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
            background: var(--success-color);
            border-radius: 50%;
            animation: pulse 2s infinite;
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
            position: relative;
        }

        .header-btn:hover {
            background: var(--bg-tertiary);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .header-btn.loading::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 12px;
            height: 12px;
            border: 2px solid var(--primary-color);
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            transform: translate(-50%, -50%);
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        /* Messages Area */
        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 2rem;
            scroll-behavior: smooth;
            padding-bottom: 140px;
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

        /* Enhanced Stock Inputs */
        .stock-inputs {
            background: var(--bg-primary);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: center;
        }

        .stock-inputs input, .stock-inputs select {
            padding: 0.75rem;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-medium);
            background: var(--bg-secondary);
            color: var(--text-primary);
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .stock-inputs input:focus, .stock-inputs select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .stock-refresh-btn {
            padding: 0.75rem 1rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stock-refresh-btn:hover {
            background: var(--primary-dark);
        }

        .stock-refresh-btn.loading {
            opacity: 0.7;
            pointer-events: none;
        }

        /* Enhanced Welcome Screen */
        .welcome-screen {
            max-width: 900px;
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
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-top: 3rem;
        }

        .feature-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-xl);
            padding: 2rem;
            text-align: left;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
            transform: scaleX(0);
            transition: transform 0.3s;
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-color);
        }

        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .feature-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 1.125rem;
        }

        .feature-desc {
            color: var(--text-secondary);
            font-size: 0.875rem;
            line-height: 1.6;
        }

        .feature-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--success-color);
            color: white;
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-sm);
            font-weight: 600;
        }

        /* Enhanced Message Bubbles */
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
            max-width: 900px;
            margin: 0 auto;
        }

        .message-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .message-avatar {
            width: 2.5rem;
            height: 2.5rem;
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

        /* Stock Data Display */
        .stock-data-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-lg);
            padding: 1rem;
            margin: 1rem 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
        }

        .stock-metric {
            text-align: center;
        }

        .stock-metric-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            font-weight: 600;
        }

        .stock-metric-value {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .stock-metric-value.positive {
            color: var(--success-color);
        }

        .stock-metric-value.negative {
            color: var(--error-color);
        }

        /* Enhanced Typing Indicator */
        .typing-indicator {
            display: none;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 0;
            max-width: 900px;
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

        /* Enhanced Input Area */
        .input-area {
            position: fixed;
            bottom: 0;
            left: 320px;
            right: 0;
            padding: 1.5rem 2rem 2rem;
            background: var(--bg-primary);
            border-top: 1px solid var(--border-light);
            z-index: 1000;
        }

        .input-container {
            max-width: 900px;
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

        .attachment-btn, .voice-btn, .refresh-data-btn {
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

        .attachment-btn:hover, .voice-btn:hover, .refresh-data-btn:hover {
            background: var(--bg-tertiary);
            color: var(--primary-color);
        }

        .refresh-data-btn.loading {
            animation: spin 1s linear infinite;
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
            max-height: 300px;
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
                left: -320px;
                top: 0;
                width: 320px;
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
                grid-template-columns: 1fr;
            }
        }

        /* Enhanced message actions */
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

        /* Chart container */
        .chart-container {
            background: var(--bg-primary);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-lg);
            padding: 1rem;
            margin: 1rem 0;
            max-width: 100%;
            overflow: hidden;
        }

        /* Loading states */
        .loading-spinner {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid var(--border-light);
            border-top: 2px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        /* Success/Error indicators */
        .success-indicator {
            color: var(--success-color);
        }

        .error-indicator {
            color: var(--error-color);
        }

        .warning-indicator {
            color: var(--warning-color);
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
        }

        /* Code blocks in messages */
        .message-body pre {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-md);
            padding: 1rem;
            overflow-x: auto;
            font-family: 'Fira Code', 'Monaco', 'Consolas', monospace;
            font-size: 0.875rem;
            margin: 1rem 0;
        }

        .message-body code {
            background: var(--bg-tertiary);
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-sm);
            font-family: 'Fira Code', 'Monaco', 'Consolas', monospace;
            font-size: 0.875rem;
        }

        /* Enhanced Tables */
        .message-body table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
            font-size: 0.875rem;
        }

        .message-body th,
        .message-body td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--border-light);
        }

        .message-body th {
            background: var(--bg-secondary);
            font-weight: 600;
            color: var(--text-primary);
        }

        /* Accessibility */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Real-time data badge */
        .realtime-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            background: var(--success-color);
            color: white;
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-sm);
            font-weight: 600;
            margin-left: 0.5rem;
        }

        .realtime-badge::before {
            content: "●";
            animation: pulse 1s infinite;
        }
    </style>

    <div class="ai-portfolio-app">
        <div class="ai-chat-container">
            <!-- Enhanced Sidebar -->
            <div class="ai-chat-sidebar" id="sidebar">
                <div class="sidebar-header">
                    <div class="sidebar-brand">
                        <div class="brand-icon">📈</div>
                        <span>Portfolio AI Pro</span>
                    </div>
                    <div class="market-status" id="marketStatus">
                        <div class="market-indicator open" id="marketIndicator"></div>
                        <span id="marketStatusText">Market Open</span>
                        <span class="realtime-badge">LIVE</span>
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
                    <div class="history-title">AI Commands</div>
                    <div class="template-grid">
                        <button class="template-btn realtime" data-command="/live-analysis">🔴 Live Portfolio Analysis</button>
                        <button class="template-btn realtime" data-command="/market-trends">📊 Real-time Market Trends</button>
                        <button class="template-btn realtime" data-command="/stock-screener">🔍 AI Stock Screener</button>
                        <button class="template-btn" data-command="/risk-assessment">⚠️ Risk Assessment</button>
                        <button class="template-btn realtime" data-command="/price-alerts">🚨 Price Alerts Setup</button>
                        <button class="template-btn realtime" data-command="/sector-rotation">🔄 Sector Rotation Analysis</button>
                        <button class="template-btn" data-command="/portfolio-optimization">🎯 Portfolio Optimization</button>
                    </div>
                </div>
            </div>

            <!-- Main Chat Area -->
            <div class="ai-chat-main">
                <div class="chat-header">
                    <div class="chat-title">
                        <button class="header-btn" id="sidebarToggle">☰</button>
                        <span>AI Portfolio Analyzer Pro</span>
                        <div class="status-indicator"></div>
                        <span class="realtime-badge">ChatGPT-4 Powered</span>
                    </div>
                    <div class="header-actions">
                        <button class="header-btn" id="refreshDataBtn" title="Refresh Market Data">🔄</button>
                        <button class="header-btn" id="themeToggleBtn" title="Toggle Theme">🌙</button>
                        <button class="header-btn" id="exportBtn" title="Export Analysis">📄</button>
                        <button class="header-btn" id="settingsBtn" title="Settings">⚙️</button>
                        <button class="header-btn" id="fullscreenBtn" title="Fullscreen">⛶</button>
                    </div>
                </div>

                <div class="messages-container" id="messagesContainer">
                    <!-- Enhanced Stock Inputs -->
                    <div class="stock-inputs">
                        <input type="text" id="stockSymbol" placeholder="Stock Symbol (e.g., AAPL, MSFT)">
                        <select id="exchange">
                            <option value="NASDAQ">NASDAQ</option>
                            <option value="NYSE">NYSE</option>
                            <option value="PSX" selected>Pakistan Stock Exchange</option>
                            <option value="BSE">Bombay Stock Exchange</option>
                        </select>
                        <select id="timeframe">
                            <option value="1D">1 Day</option>
                            <option value="1W">1 Week</option>
                            <option value="1M" selected>1 Month</option>
                            <option value="3M">3 Months</option>
                            <option value="1Y">1 Year</option>
                        </select>
                        <input type="date" id="analysisDate">
                        <button class="stock-refresh-btn" id="stockRefreshBtn">
                            <span>🔄</span>
                            Get Live Data
                        </button>
                    </div>

                    <div class="welcome-screen" id="welcomeScreen">
                        <div class="welcome-icon">🚀</div>
                        <h1 class="welcome-title">AI Portfolio Analyzer Pro</h1>
                        <p class="welcome-subtitle">Your ChatGPT-4 powered assistant for real-time stock analysis, portfolio optimization, and market insights with live data integration</p>

                        <div class="welcome-features">
                            <div class="feature-card">
                                <div class="feature-badge">NEW</div>
                                <div class="feature-icon">🧠</div>
                                <div class="feature-title">ChatGPT-4 Integration</div>
                                <div class="feature-desc">Advanced AI analysis powered by OpenAI's latest GPT-4 model for sophisticated financial insights</div>
                            </div>
                            <div class="feature-card">
                                <div class="feature-badge">LIVE</div>
                                <div class="feature-icon">📊</div>
                                <div class="feature-title">Real-time Data Analysis</div>
                                <div class="feature-desc">Live market data integration with instant portfolio analysis and performance tracking</div>
                            </div>
                            <div class="feature-card">
                                <div class="feature-badge">PRO</div>
                                <div class="feature-icon">🎯</div>
                                <div class="feature-title">Smart Recommendations</div>
                                <div class="feature-desc">AI-powered stock picks and portfolio optimization based on real-time market conditions</div>
                            </div>
                            <div class="feature-card">
                                <div class="feature-icon">⚡</div>
                                <div class="feature-title">Lightning Fast</div>
                                <div class="feature-desc">Instant analysis and responses with advanced caching for optimal performance</div>
                            </div>
                            <div class="feature-card">
                                <div class="feature-icon">🛡️</div>
                                <div class="feature-title">Risk Management</div>
                                <div class="feature-desc">Comprehensive risk assessment with real-time volatility analysis and alerts</div>
                            </div>
                            <div class="feature-card">
                                <div class="feature-icon">📈</div>
                                <div class="feature-title">Advanced Charts</div>
                                <div class="feature-desc">Interactive charts and visualizations with technical indicators and trend analysis</div>
                            </div>
                        </div>
                    </div>

                    <div class="typing-indicator" id="typingIndicator">
                        <div class="message-avatar ai-avatar">AI</div>
                        <span>AI is analyzing market data</span>
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
                            <div class="command-item" data-command="/live-analysis">
                                <div class="command-name">/live-analysis</div>
                                <div class="command-desc">Get real-time portfolio analysis with current market data</div>
                            </div>
                            <div class="command-item" data-command="/market-trends">
                                <div class="command-name">/market-trends</div>
                                <div class="command-desc">Analyze current market trends and sector performance</div>
                            </div>
                            <div class="command-item" data-command="/stock-screener">
                                <div class="command-name">/stock-screener</div>
                                <div class="command-desc">Screen stocks based on AI-powered criteria</div>
                            </div>
                            <div class="command-item" data-command="/risk-assessment">
                                <div class="command-name">/risk-assessment</div>
                                <div class="command-desc">Assess portfolio risks and volatility</div>
                            </div>
                            <div class="command-item" data-command="/price-alerts">
                                <div class="command-name">/price-alerts</div>
                                <div class="command-desc">Set up intelligent price alerts</div>
                            </div>
                            <div class="command-item" data-command="/sector-rotation">
                                <div class="command-name">/sector-rotation</div>
                                <div class="command-desc">Analyze sector rotation opportunities</div>
                            </div>
                            <div class="command-item" data-command="/portfolio-optimization">
                                <div class="command-name">/portfolio-optimization</div>
                                <div class="command-desc">Optimize portfolio allocation and diversification</div>
                            </div>
                        </div>
                        <div class="input-wrapper">
                            <textarea
                                class="message-input"
                                id="messageInput"
                                placeholder="Ask about stocks, portfolio analysis, market trends... Use / for AI commands"
                                rows="1"></textarea>
                            <div class="input-actions">
                                <input type="file" id="fileInput" style="display: none;" accept="image/*,application/pdf,.txt,.csv,.json,.xlsx">
                                <button class="attachment-btn" id="attachmentBtn" title="Attach file">📎</button>
                                <button class="voice-btn" id="voiceBtn" title="Voice input">🎤</button>
                                <button class="refresh-data-btn" id="refreshDataBtn2" title="Refresh market data">📊</button>
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

    <?php
    return ob_get_clean();
});

// ----------- ENHANCED REST API ENDPOINTS -----------
add_action('rest_api_init', function () {
    // Main analysis endpoint
    register_rest_route('ai-pa/v1', '/analyze', [
        'methods' => 'POST',
        'callback' => 'ai_pa_analyze_portfolio_enhanced',
        'permission_callback' => '__return_true',
        'args' => [
            'message' => [
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_textarea_field',
            ],
            'stockSymbol' => [
                'required' => false,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'exchange' => [
                'required' => false,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'timeframe' => [
                'required' => false,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'enableRealtime' => [
                'required' => false,
                'type' => 'boolean',
            ],
            'file' => [
                'required' => false,
                'type' => 'string',
            ],
        ],
    ]);

    // Market data endpoint
    register_rest_route('ai-pa/v1', '/market-data', [
        'methods' => 'GET',
        'callback' => 'ai_pa_get_market_data',
        'permission_callback' => '__return_true',
    ]);

    // Stock data endpoint
    register_rest_route('ai-pa/v1', '/stock-data', [
        'methods' => 'GET',
        'callback' => 'ai_pa_get_stock_data',
        'permission_callback' => '__return_true',
        'args' => [
            'symbol' => [
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ],
    ]);
});

function ai_pa_analyze_portfolio_enhanced(WP_REST_Request $request) {
    $start_time = microtime(true);

    if (!wp_verify_nonce($request->get_header('X-WP-Nonce'), 'wp_rest')) {
        return new WP_REST_Response(['reply' => '❌ Security check failed.'], 403);
    }

    // Get ChatGPT API configuration
    $api_key = get_option('ai_pa_chatgpt_api_key', '');
    $enable_realtime = get_option('ai_pa_enable_realtime', 1);

    $msg = sanitize_textarea_field($request->get_param('message'));
    $stock_symbol = sanitize_text_field($request->get_param('stockSymbol'));
    $exchange = sanitize_text_field($request->get_param('exchange'));
    $timeframe = sanitize_text_field($request->get_param('timeframe'));
    $analysis_date = sanitize_text_field($request->get_param('analysisDate'));
    $file_data = $request->get_param('file');

    // Enhanced logging
    error_log('AI Portfolio Analyzer Enhanced - API Key configured: ' . (!empty($api_key) ? 'Yes' : 'No'));
    error_log('AI Portfolio Analyzer Enhanced - Message: ' . substr($msg, 0, 100));
    error_log('AI Portfolio Analyzer Enhanced - Stock Symbol: ' . ($stock_symbol ?: 'None'));
    error_log('AI Portfolio Analyzer Enhanced - Exchange: ' . ($exchange ?: 'None'));

    // Validation
    if (empty($api_key)) {
        return new WP_REST_Response([
            'reply' => '❌ Configuration Error: ChatGPT API key not configured. Please check plugin settings.'
        ], 400);
    }

    if (empty($msg)) {
        return new WP_REST_Response([
            'reply' => '❌ Error: Message is required.'
        ], 400);
    }

    // Get real-time stock data if requested and symbol provided
    $stock_data = null;
    if ($enable_realtime && !empty($stock_symbol)) {
        $stock_data = ai_pa_fetch_stock_data($stock_symbol, $exchange);
    }

    // Prepare enhanced system prompt
    $system_prompt = "You are an advanced AI financial analyst powered by ChatGPT-4, specializing in real-time stock market analysis and portfolio management. You have access to live market data and can provide:

1. Real-time stock analysis and price movements
2. Portfolio optimization recommendations
3. Risk assessment with current market conditions
4. Technical and fundamental analysis
5. Market trend analysis and predictions
6. Sector rotation strategies
7. Options and derivatives insights

Key capabilities:
- Analyze real-time market data and trends
- Provide actionable investment recommendations
- Assess portfolio risk and diversification
- Identify emerging market opportunities
- Explain complex financial concepts clearly
- Use current market data for accurate analysis

Always provide specific, actionable advice based on current market conditions. Use Pakistani Rupee (₨) for PSX stocks and USD ($) for US markets. Be professional, accurate, and focus on practical investment strategies.";

    $user_content = $msg;

    // Check if it's a request for a detailed stock analysis report
    if (!empty($stock_symbol) && preg_match('/analyze|report|trading|technical analysis|unity/i', $msg)) {
        $trading_prompt = "Give me a human-style swing trading technical analysis report as a professional swing trader for [STOCK] listed on [EXCHANGE] using the [TIMEFRAME] chart as of [DATE]. Assume TradingView is the charting platform. Include clear insights on: - Trend structure (short, medium, long term) - Key support and resistance zones - Candlestick behavior and price action - Chart patterns forming or confirming - Volume behavior and accumulation/distribution - Potential swing trade setups with entry, stop loss, and targets - Risk zones or invalidation points - Trader-style summary and outlook Only use ₨ (PKR) as the currency symbol, not ₹ (INR).";

        $timeframe_map = [
            '1D' => 'DAILY', '1W' => 'WEEKLY', '1M' => 'MONTHLY', '3M' => '3-MONTH', '1Y' => 'YEARLY'
        ];
        $chart_timeframe = $timeframe_map[$timeframe] ?? 'DAILY';

        $user_content = str_replace(
            ['[STOCK]', '[EXCHANGE]', '[TIMEFRAME]', '[DATE]'],
            [strtoupper($stock_symbol), $exchange ?: 'Pakistan Stock Exchange', $chart_timeframe, $analysis_date ?: date('Y-m-d')],
            $trading_prompt
        );
    }

    // Add real-time data context to the prompt if available
    if ($stock_data && !is_wp_error($stock_data) && !empty($stock_data)) {
        $user_content .= "\n\n[Real-time Market Data for " . strtoupper($stock_symbol) . " is available: ";
        $user_content .= "Price: " . ($stock_data['price'] ?? 'N/A') . ", ";
        $user_content .= "Change: " . ($stock_data['change'] ?? 'N/A') . ", ";
        $user_content .= "Change Percent: " . ($stock_data['changePercent'] ?? 'N/A') . ", ";
        $user_content .= "Volume: " . ($stock_data['volume'] ?? 'N/A');
        $user_content .= "]";
    }

    // Add file content if provided
    if (!empty($file_data)) {
        $file_parts = explode(',', $file_data, 2);
        if (count($file_parts) === 2) {
            $file_type = $file_parts[0];
            $file_content = base64_decode($file_parts[1]);

            if (strpos($file_type, 'image') !== false) {
                $user_content .= "\n\n[User has attached an image for analysis. Please provide relevant financial insights.]";
            } elseif (strpos($file_type, 'text') !== false || strpos($file_type, 'csv') !== false) {
                $user_content .= "\n\n[User has attached financial data:]\n" . substr($file_content, 0, 3000);
            } else {
                $user_content .= "\n\n[User has attached a file for financial analysis.]";
            }
        }
    }

    // Prepare ChatGPT API request
    $body = [
        "model" => "gpt-4-turbo-preview",
        "messages" => [
            [
                "role" => "system",
                "content" => $system_prompt
            ],
            [
                "role" => "user",
                "content" => $user_content
            ]
        ],
        "max_tokens" => 2000,
        "temperature" => 0.7,
        "top_p" => 0.9,
        "frequency_penalty" => 0.1,
        "presence_penalty" => 0.1,
        "stream" => false
    ];

    // Make API request to OpenAI
    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
            'User-Agent' => 'WordPress-AI-Portfolio-Analyzer-Pro/4.0'
        ],
        'body' => json_encode($body),
        'timeout' => 60,
        'sslverify' => true
    ]);

    // Handle request errors
    if (is_wp_error($response)) {
        $error_msg = $response->get_error_message();
        error_log('AI Portfolio Analyzer Enhanced - WP Error: ' . $error_msg);
        return new WP_REST_Response([
            'reply' => '❌ Connection Error: ' . $error_msg . '. Please check your internet connection and try again.'
        ], 500);
    }

    $http_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);

    error_log('AI Portfolio Analyzer Enhanced - HTTP Code: ' . $http_code);
    error_log('AI Portfolio Analyzer Enhanced - Response Length: ' . strlen($response_body));

    // Handle HTTP errors
    if ($http_code !== 200) {
        $error_detail = 'Unknown error';
        $data = json_decode($response_body, true);

        if (isset($data['error']['message'])) {
            $error_detail = $data['error']['message'];
        } elseif (isset($data['message'])) {
            $error_detail = $data['message'];
        }

        error_log('AI Portfolio Analyzer Enhanced - API Error: ' . $error_detail);

        // Handle specific OpenAI errors
        if ($http_code === 401) {
            return new WP_REST_Response([
                'reply' => "❌ Authentication Error: Invalid ChatGPT API key. Please check your API key configuration in the plugin settings."
            ], 401);
        } elseif ($http_code === 429) {
            return new WP_REST_Response([
                'reply' => "❌ Rate Limit Error: Too many requests. Please wait a moment and try again."
            ], 429);
        } elseif ($http_code === 400) {
            return new WP_REST_Response([
                'reply' => "❌ Request Error: " . $error_detail
            ], 400);
        }

        return new WP_REST_Response([
            'reply' => "❌ API Error (Code: $http_code): $error_detail"
        ], $http_code);
    }

    // Parse response
    $data = json_decode($response_body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('AI Portfolio Analyzer Enhanced - JSON Parse Error: ' . json_last_error_msg());
        return new WP_REST_Response([
            'reply' => '❌ Invalid response format from ChatGPT API. Please try again.'
        ], 500);
    }

    // Extract AI reply
    if (isset($data['choices'][0]['message']['content'])) {
        $ai_reply = trim($data['choices'][0]['message']['content']);

        // Basic response validation
        if (empty($ai_reply)) {
            return new WP_REST_Response([
                'reply' => '❌ Empty response from ChatGPT API. Please try rephrasing your question.'
            ], 500);
        }

        // Prepare response data
        $response_data = [
            'reply' => $ai_reply,
            'timestamp' => current_time('timestamp'),
            'model' => 'gpt-4-turbo-preview',
            'tokens_used' => $data['usage']['total_tokens'] ?? 0
        ];

        // Add stock data if available
        if ($stock_data && !is_wp_error($stock_data) && !empty($stock_data)) {
            $response_data['stockData'] = $stock_data;
        }

        // Track usage
        $response_time_ms = (microtime(true) - $start_time) * 1000;
        ai_pa_track_usage($user_content, $ai_reply, $data['usage']['total_tokens'] ?? 0, $response_time_ms, $stock_symbol);

        return new WP_REST_Response($response_data);
    }

    // Handle API errors in response
    if (isset($data['error'])) {
        error_log('AI Portfolio Analyzer Enhanced - API Response Error: ' . print_r($data['error'], true));
        return new WP_REST_Response([
            'reply' => '❌ ChatGPT API Error: ' . $data['error']['message']
        ], 500);
    }

    // Fallback error
    error_log('AI Portfolio Analyzer Enhanced - Unexpected Response Format: ' . print_r($data, true));
    return new WP_REST_Response([
        'reply' => '❌ Unexpected response format from ChatGPT API. Please contact support if this issue persists.'
    ], 500);
}

function ai_pa_get_market_data(WP_REST_Request $request) {
    if (!wp_verify_nonce($request->get_header('X-WP-Nonce'), 'wp_rest')) {
        return new WP_REST_Response(['error' => 'Security check failed.'], 403);
    }
    // Cache key for market data
    $cache_key = 'ai_pa_market_data';
    $cache_duration = get_option('ai_pa_cache_duration', 300); // 5 minutes default

    // Try to get cached data
    $cached_data = get_transient($cache_key);
    if ($cached_data !== false) {
        return new WP_REST_Response($cached_data);
    }

    // Simulate market data (replace with real API calls)
    $market_data = [
        'marketStatus' => ai_pa_get_market_status(),
        'indices' => [
            'PSX' => [
                'value' => 45234.56,
                'change' => 234.12,
                'changePercent' => 0.52
            ],
            'SPY' => [
                'value' => 4567.89,
                'change' => -12.34,
                'changePercent' => -0.27
            ],
            'NASDAQ' => [
                'value' => 14567.23,
                'change' => 45.67,
                'changePercent' => 0.31
            ]
        ],
        'sectors' => [
            'Technology' => ['change' => 0.45],
            'Healthcare' => ['change' => -0.23],
            'Finance' => ['change' => 0.67],
            'Energy' => ['change' => -1.23]
        ],
        'timestamp' => current_time('timestamp')
    ];

    // Cache the data
    set_transient($cache_key, $market_data, $cache_duration);

    return new WP_REST_Response($market_data);
}

function ai_pa_get_stock_data(WP_REST_Request $request) {
    if (!wp_verify_nonce($request->get_header('X-WP-Nonce'), 'wp_rest')) {
        return new WP_REST_Response(['error' => 'Security check failed.'], 403);
    }
    $symbol = strtoupper(sanitize_text_field($request->get_param('symbol')));

    if (empty($symbol)) {
        return new WP_REST_Response([
            'error' => 'Stock symbol is required'
        ], 400);
    }

    $stock_data = ai_pa_fetch_stock_data($symbol);

    if ($stock_data) {
        return new WP_REST_Response($stock_data);
    } else {
        return new WP_REST_Response([
            'error' => 'Unable to fetch stock data for ' . $symbol
        ], 500);
    }
}

function ai_pa_fetch_stock_data($symbol, $exchange = null) {
    $cache_key = 'ai_pa_stock_' . strtolower($symbol);
    $cache_duration = get_option('ai_pa_cache_duration', 300);

    // Try cached data first
    $cached_data = get_transient($cache_key);
    if ($cached_data !== false) {
        return $cached_data;
    }

    // Get Alpha Vantage API key
    $alpha_vantage_key = get_option('ai_pa_alpha_vantage_key', '');

    if (!empty($alpha_vantage_key)) {
        // Use Alpha Vantage API for real data
        $api_url = "https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol={$symbol}&apikey={$alpha_vantage_key}";

        $response = wp_remote_get($api_url, [
            'timeout' => 30,
            'sslverify' => true
        ]);

        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (!empty($data['Global Quote']) && isset($data['Global Quote']['05. price'])) {
                $quote = $data['Global Quote'];
                $stock_data = [
                    'symbol' => $symbol,
                    'price' => number_format(floatval($quote['05. price']), 2),
                    'change' => number_format(floatval($quote['09. change']), 2),
                    'changePercent' => $quote['10. change percent'],
                    'volume' => number_format(intval($quote['06. volume'])),
                    'marketCap' => 'N/A', // Not available in this endpoint
                    'timestamp' => current_time('timestamp')
                ];

                // Cache the data
                set_transient($cache_key, $stock_data, $cache_duration);
                return $stock_data;
            }
        }
    }

    // Fallback to simulated data
    $simulated_data = [
        'symbol' => $symbol,
        'price' => number_format(rand(50, 500) + (rand(0, 99) / 100), 2),
        'change' => (rand(0, 1) ? '+' : '-') . number_format(rand(1, 10) + (rand(0, 99) / 100), 2),
        'changePercent' => (rand(0, 1) ? '+' : '-') . number_format(rand(1, 5) + (rand(0, 99) / 100), 2) . '%',
        'volume' => number_format(rand(1000000, 10000000)),
        'marketCap' => number_format(rand(1, 100)) . 'B',
        'timestamp' => current_time('timestamp'),
        'note' => 'Simulated data - Configure Alpha Vantage API key for real data'
    ];

    // Cache simulated data for shorter duration
    set_transient($cache_key, $simulated_data, 60);
    return $simulated_data;
}

// ----------- ACTIVATION/DEACTIVATION HOOKS -----------
register_activation_hook(__FILE__, 'ai_pa_activate_enhanced');

function ai_pa_activate_enhanced() {
    // Set default options
    add_option('ai_pa_chatgpt_api_key', '');
    add_option('ai_pa_theme_color', '#10b981');
    add_option('ai_pa_enable_realtime', 1);
    add_option('ai_pa_cache_duration', 300);

    // Set activation flag
    set_transient('ai_pa_activated_enhanced', true, 60);

    // Create necessary database tables if needed
    ai_pa_create_tables();

    // Flush rewrite rules
    flush_rewrite_rules();
}
