document.addEventListener('DOMContentLoaded', function() {
    // Initialize the Enhanced Portfolio AI Chat Interface
    class EnhancedPortfolioAI {
        constructor() {
            this.initializeElements();
            this.bindEvents();
            this.chats = this.loadChats();
            this.activeChatId = null;
            this.marketData = {};
            this.sessionStats = {
                queries: 0,
                responseTimes: [],
                sessionStart: Date.now(),
                dataRefreshes: 0
            };
            this.commands = {
                '/live-analysis': 'Analyze my portfolio with real-time market data and provide current performance insights, including today\'s gains/losses, market sentiment, and actionable recommendations.',
                '/market-trends': 'Show me the latest market trends, sector performance, and emerging opportunities based on current market conditions and trading volumes.',
                '/stock-screener': 'Help me screen stocks based on specific criteria like P/E ratio, market cap, volume, and technical indicators. Provide top recommendations.',
                '/risk-assessment': 'Assess the risk profile of my portfolio including volatility analysis, beta calculations, and diversification recommendations.',
                '/price-alerts': 'Set up intelligent price alerts for my stocks based on technical analysis, support/resistance levels, and market momentum.',
                '/sector-rotation': 'Analyze sector rotation opportunities and recommend which sectors to overweight or underweight based on current market cycle.',
                '/portfolio-optimization': 'Optimize my portfolio allocation using modern portfolio theory, considering risk tolerance and market conditions.'
            };
            this.selectedCommandIndex = -1;
            this.renderChatHistory();
            this.startNewChat();
            this.applyInitialTheme();
            this.initializeMarketStatus();
            this.startDataRefreshInterval();

            // Set today's date as default
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('analysisDate').value = today;
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
            this.refreshDataBtn = document.getElementById('refreshDataBtn');
            this.refreshDataBtn2 = document.getElementById('refreshDataBtn2');
            this.stockRefreshBtn = document.getElementById('stockRefreshBtn');
            this.marketStatus = document.getElementById('marketStatus');
            this.marketIndicator = document.getElementById('marketIndicator');
            this.marketStatusText = document.getElementById('marketStatusText');
        }

        bindEvents() {
            // Send message events
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

            // Data refresh buttons
            this.refreshDataBtn.addEventListener('click', () => this.refreshMarketData());
            this.refreshDataBtn2.addEventListener('click', () => this.refreshMarketData());
            this.stockRefreshBtn.addEventListener('click', () => this.refreshStockData());

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
            const stockSymbol = document.getElementById('stockSymbol').value;
            const exchange = document.getElementById('exchange').value;
            const timeframe = document.getElementById('timeframe').value;
            const analysisDate = document.getElementById('analysisDate').value;

            let textMessage = message || this.messageInput.value.trim();
            if (!textMessage && !fileData) return;

            // Disable send button to prevent multiple sends
            this.sendBtn.disabled = true;

            // Enhanced context with stock data
            let contextualMessage = textMessage;
            if (stockSymbol || exchange || timeframe || analysisDate) {
                contextualMessage += `\n\n[Context: Stock Symbol: ${stockSymbol || 'Not specified'}, Exchange: ${exchange}, Timeframe: ${timeframe}, Date: ${analysisDate}]`;
            }

            // Add real-time market context if available
            if (Object.keys(this.marketData).length > 0) {
                contextualMessage += `\n[Market Data Available: ${Object.keys(this.marketData).join(', ')}]`;
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
                const body = {
                    message: contextualMessage,
                    stockSymbol: stockSymbol,
                    exchange: exchange,
                    timeframe: timeframe,
                    analysisDate: analysisDate,
                    enableRealtime: true
                };

                if (fileData) {
                    body.file = fileData;
                }

                const response = await fetch(aiPA.ajax_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': aiPA.nonce
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

                // Add stock data visualization if available
                if (data.stockData) {
                    this.displayStockData(data.stockData);
                }

            } catch (error) {
                this.showTyping(false);
                this.addMessage('🔴 Connection Error: Unable to reach AI service. Please check your internet connection and try again.', 'ai');
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
                        <span class="message-sender">${type === 'user' ? 'You' : 'Portfolio AI Pro'}</span>
                        <span class="message-time">${timeString}</span>
                        ${type === 'ai' ? '<span class="realtime-badge">ChatGPT-4</span>' : ''}
                    </div>
                    <div class="message-body">
                        ${this.formatMessage(content)}
                    </div>
                    <div class="message-actions">
                        <button class="action-btn copy-btn">📋 Copy</button>
                        ${type === 'ai' ? '<button class="action-btn regenerate-btn">🔄 Regenerate</button>' : ''}
                        <button class="action-btn share-btn">🔗 Share</button>
                        ${type === 'ai' ? '<button class="action-btn analyze-btn">📊 Deep Analyze</button>' : ''}
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
                        chat.title = content.substring(0, 40) + (content.length > 40 ? '...' : '');
                    }
                    chat.timestamp = Date.now();
                }
            }
        }

        formatMessage(content) {
            // Enhanced markdown-like formatting with financial data support
            return content
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/`(.*?)`/g, '<code>$1</code>')
                .replace(/\$([0-9,]+\.?[0-9]*)/g, '<span class="success-indicator">$$1</span>')
                .replace(/(\+[0-9,]+\.?[0-9]*%?)/g, '<span class="success-indicator">$1</span>')
                .replace(/(\-[0-9,]+\.?[0-9]*%?)/g, '<span class="error-indicator">$1</span>')
                .replace(/\n/g, '<br>');
        }

        displayStockData(stockData) {
            const stockCard = document.createElement('div');
            stockCard.className = 'stock-data-card';

            const metrics = [
                { label: 'Price', value: stockData.price, class: 'neutral' },
                { label: 'Change', value: stockData.change > 0 ? 'positive' : 'negative' },
                { label: 'Volume', value: stockData.volume, class: 'neutral' },
                { label: 'Market Cap', value: stockData.marketCap, class: 'neutral' }
            ];

            stockCard.innerHTML = metrics.map(metric => `
                <div class="stock-metric">
                    <div class="stock-metric-label">${metric.label}</div>
                    <div class="stock-metric-value ${metric.class}">${metric.value}</div>
                </div>
            `).join('');

            // Add to the last AI message
            const lastAiMessage = this.messagesContainer.querySelector('.message.ai:last-of-type .message-body');
            if (lastAiMessage) {
                lastAiMessage.appendChild(stockCard);
            }
        }

        bindMessageActions(messageDiv) {
            const copyBtn = messageDiv.querySelector('.copy-btn');
            const regenerateBtn = messageDiv.querySelector('.regenerate-btn');
            const shareBtn = messageDiv.querySelector('.share-btn');
            const analyzeBtn = messageDiv.querySelector('.analyze-btn');

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

            if (analyzeBtn) {
                analyzeBtn.addEventListener('click', () => {
                    this.deepAnalyze(messageDiv);
                });
            }
        }

        async refreshMarketData() {
            this.refreshDataBtn.classList.add('loading');
            this.refreshDataBtn2.classList.add('loading');

            try {
                const response = await fetch(aiPA.market_data_url, {
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': aiPA.nonce
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.marketData = data;
                    this.sessionStats.dataRefreshes++;
                    this.updateMarketStatus(data.marketStatus);

                    // Show success message
                    this.addMessage('✅ Market data refreshed successfully. Latest market information is now available for analysis.', 'ai');
                }
            } catch (error) {
                console.error('Error refreshing market data:', error);
                this.addMessage('❌ Failed to refresh market data. Please try again later.', 'ai');
            } finally {
                this.refreshDataBtn.classList.remove('loading');
                this.refreshDataBtn2.classList.remove('loading');
            }
        }

        async refreshStockData() {
            const stockSymbol = document.getElementById('stockSymbol').value;
            if (!stockSymbol) {
                alert('Please enter a stock symbol first.');
                return;
            }

            this.stockRefreshBtn.classList.add('loading');
            this.stockRefreshBtn.textContent = 'Loading...';

            try {
                const response = await fetch(`${aiPA.stock_data_url}?symbol=${stockSymbol}`, {
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': aiPA.nonce
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.displayStockData(data);
                    this.addMessage(`📊 Real-time data for ${stockSymbol.toUpperCase()} has been loaded and is ready for analysis.`, 'ai');
                }
            } catch (error) {
                console.error('Error refreshing stock data:', error);
                this.addMessage(`❌ Failed to load data for ${stockSymbol.toUpperCase()}. Please check the symbol and try again.`, 'ai');
            } finally {
                this.stockRefreshBtn.classList.remove('loading');
                this.stockRefreshBtn.innerHTML = '<span>🔄</span> Get Live Data';
            }
        }

        updateMarketStatus(status) {
            if (!status) return;

            this.marketIndicator.className = `market-indicator ${status.toLowerCase()}`;
            this.marketStatusText.textContent = `Market ${status}`;

            const statusMessages = {
                'open': '🟢 Market is currently open for trading',
                'closed': '🔴 Market is currently closed',
                'pre-market': '🟡 Pre-market trading session'
            };

            if (statusMessages[status.toLowerCase()]) {
                // Update market status in UI
                console.log(statusMessages[status.toLowerCase()]);
            }
        }

        initializeMarketStatus() {
            // Set initial market status based on time
            const now = new Date();
            const hour = now.getHours();

            if (hour >= 9 && hour < 16) {
                this.updateMarketStatus('open');
            } else if (hour >= 4 && hour < 9) {
                this.updateMarketStatus('pre-market');
            } else {
                this.updateMarketStatus('closed');
            }
        }

        startDataRefreshInterval() {
            // Refresh market data every 5 minutes during market hours
            setInterval(() => {
                const now = new Date();
                const hour = now.getHours();

                if (hour >= 9 && hour < 16) {
                    this.refreshMarketData();
                }
            }, 5 * 60 * 1000); // 5 minutes
        }

        deepAnalyze(messageDiv) {
            const messageText = messageDiv.querySelector('.message-body').textContent;
            const analysisPrompt = `Please provide a deeper analysis of this information: "${messageText}". Include technical analysis, fundamental analysis, risk assessment, and specific actionable recommendations.`;

            this.messageInput.value = analysisPrompt;
            this.sendMessage();
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
                title: 'New Analysis Session',
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
            if (confirm('Are you sure you want to delete this analysis session?')) {
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
                alert('No analysis session to export yet. Start chatting with the AI first!');
                return;
            }

            const chat = this.chats[this.activeChatId];
            const exportData = {
                exportDate: new Date().toISOString(),
                sessionTitle: chat.title,
                aiModel: 'ChatGPT-4 Turbo',
                totalQueries: this.sessionStats.queries,
                dataRefreshes: this.sessionStats.dataRefreshes,
                averageResponseTime: this.sessionStats.responseTimes.length > 0 ?
                    (this.sessionStats.responseTimes.reduce((a, b) => a + b) / this.sessionStats.responseTimes.length / 1000).toFixed(2) + 's' : 'N/A',
                marketData: this.marketData,
                messages: chat.messages,
                sessionStats: this.sessionStats
            };

            const dataStr = JSON.stringify(exportData, null, 2);
            const dataBlob = new Blob([dataStr], {type: 'application/json'});
            const url = URL.createObjectURL(dataBlob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `AI_Portfolio_Analysis_${new Date().toISOString().split('T')[0]}_${Date.now()}.json`;
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
            const lastUserMessageIndex = this.findLastIndex(chat.messages, msg => msg.type === 'user');
            if (lastUserMessageIndex === -1) return;

            const lastUserMessage = chat.messages[lastUserMessageIndex];

            // Remove the last AI response if it exists
            const lastAiMessageIndex = this.findLastIndex(chat.messages, msg => msg.type === 'ai');
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
                title: 'AI Portfolio Analysis - ChatGPT-4 Powered',
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
                const chats = localStorage.getItem('ai_pa_chats_v4');
                return chats ? JSON.parse(chats) : {};
            } catch (error) {
                console.error('Error loading chats:', error);
                return {};
            }
        }

        saveChats() {
            try {
                localStorage.setItem('ai_pa_chats_v4', JSON.stringify(this.chats));
            } catch (error) {
                console.error('Error saving chats:', error);
            }
        }

        renderChatHistory() {
            this.chatHistory.innerHTML = '';
            const chats = Object.values(this.chats).sort((a, b) => b.timestamp - a.timestamp);

            if (chats.length === 0) {
                this.chatHistory.innerHTML = '<div class="history-item">No recent sessions</div>';
                return;
            }

            const today = new Date().toDateString();
            const yesterday = new Date(Date.now() - 86400000).toDateString();

            let currentSection = '';
            let currentContainer = null;

            chats.forEach(chat => {
                const chatDate = new Date(chat.timestamp).toDateString();
                let sectionTitle = 'Older Sessions';

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
                    <button class="delete-chat" title="Delete session">×</button>
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

            if (file.size > 10 * 1024 * 1024) { // 10MB limit
                alert('File size too large. Please select a file smaller than 10MB.');
                return;
            }

            const reader = new FileReader();
            reader.onload = (event) => {
                const fileData = event.target.result;
                this.sendMessage(`I've attached a file: ${file.name} (${(file.size / 1024).toFixed(2)}KB). Please analyze this financial data and provide insights.`, fileData);
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

    // Initialize the Enhanced Portfolio AI
    window.enhancedPortfolioAI = new EnhancedPortfolioAI();

    // Add welcome animations
    setTimeout(() => {
        const features = document.querySelectorAll('.feature-card');
        features.forEach((feature, index) => {
            setTimeout(() => {
                feature.style.opacity = '0';
                feature.style.transform = 'translateY(20px)';
                feature.style.animation = 'slideUp 0.6s ease-out forwards';
            }, index * 150);
        });
    }, 500);

    // Handle browser events
    window.addEventListener('popstate', function() {
        // Handle navigation if needed
    });

    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            // Page is hidden, pause operations
        } else {
            // Page is visible, resume operations
            if (window.enhancedPortfolioAI) {
                window.enhancedPortfolioAI.refreshMarketData();
            }
        }
    });

    window.addEventListener('online', function() {
        console.log('Connection restored - refreshing data');
        if (window.enhancedPortfolioAI) {
            window.enhancedPortfolioAI.refreshMarketData();
        }
    });

    window.addEventListener('offline', function() {
        console.log('Connection lost - operating in offline mode');
    });
});
