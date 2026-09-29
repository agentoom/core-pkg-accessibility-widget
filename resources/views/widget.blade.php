(function() {
    const config = window.AgentoomAccessibility;
    if (!config) return;

    const translations = @json($translations ?? []);
    const defaultLocale = '{{ $defaultLocale ?? 'en' }}';

    class AgentoomAccessibilityWidget extends HTMLElement {
        constructor() {
            super();
            this.attachShadow({ mode: 'open' });
            const supportedLocales = @json($supportedLocales);
            const docLang = document.documentElement.lang ? document.documentElement.lang.split('-')[0].toLowerCase() : null;
            const configLocale = config.locale ? config.locale.toLowerCase() : null;

            this.state = {
                isOpen: false,
                settings: {},
                activeSettings: {},
                locale: localStorage.getItem('agentoom_acc_locale') ||
                        (supportedLocales.includes(configLocale) ? configLocale :
                        (supportedLocales.includes(docLang) ? docLang :
                        (supportedLocales.includes('en') ? 'en' : defaultLocale))),
                websiteLocale: docLang || configLocale || defaultLocale || 'en',
                expandedSections: {
                    language: true,
                    profiles: false,
                    options: true
                },
                availableLanguages: @json($availableLanguages)
            };
        }

        t(key) {
            return translations[this.state.locale]?.[key] || translations['en']?.[key] || key;
        }

        async connectedCallback() {
            await this.loadConfig();
            this.render();
            this.applySettings();
        }


        async loadConfig() {
            try {
                const response = await fetch(`${config.apiUrl}/config/${config.slug}`);
                const data = await response.json();
                this.state.settings = data.settings;
                this.state.position = data.position;
                this.state.icon_trigger = data.icon_trigger;

                // Initialize active settings from localStorage or defaults
                const saved = localStorage.getItem(`agentoom_acc_${config.slug}`);
                this.state.activeSettings = saved ? JSON.parse(saved) : {};

                const defaults = this.getDefaultSettings(data.settings);
                Object.keys(defaults).forEach(key => {
                    if (this.state.activeSettings[key] === undefined) {
                        this.state.activeSettings[key] = defaults[key];
                    }
                });
            } catch (e) {
                console.error('Failed to load Agentoom Accessibility config', e);
            }
        }

        getDefaultSettings(settings) {
            const defaults = {};
            Object.keys(settings).forEach(key => {
                if (typeof settings[key] === 'boolean') {
                    defaults[key] = false;
                } else {
                    defaults[key] = settings[key];
                }
            });
            return defaults;
        }

        getPositionStyles() {
            const pos = this.state.position || 'bottom-right';
            let styles = '';
            if (pos.includes('bottom')) styles += 'bottom: 20px;';
            else styles += 'top: 20px;';
            if (pos.includes('right')) styles += 'right: 20px;';
            else styles += 'left: 20px;';
            return styles;
        }

        getPanelPositionStyles() {
            const pos = this.state.position || 'bottom-right';
            let styles = '';
            if (pos.includes('bottom')) styles += 'bottom: 80px;';
            else styles += 'top: 80px;';
            if (pos.includes('right')) styles += 'right: 0;';
            else styles += 'left: 0;';
            return styles;
        }

        applySettings() {
            const s = this.state.activeSettings;
            const root = document.documentElement;

            // Contrast & Saturation & Monochrome
            let filters = [];
            if (s.contrast) filters.push('contrast(150%)');
            if (s.saturation) filters.push('saturate(200%)');
            if (s.monochrome) filters.push('grayscale(100%)');

            if (filters.length > 0) {
                root.style.filter = filters.join(' ');
            } else {
                root.style.filter = '';
            }

            if (s.contrast) {
                root.classList.add('agentoom-high-contrast');
                this.injectGlobalStyles('high-contrast', `
                    .agentoom-high-contrast body {
                        background-color: #000 !important;
                        color: #fff !important;
                    }
                `);
            } else {
                root.classList.remove('agentoom-high-contrast');
                this.removeGlobalStyles('high-contrast');
            }

            // Font Size
            root.style.fontSize = `${s.font_size || 100}%`;

            // Dyslexic Font
            if (s.dyslexic_font) {
                this.injectGlobalStyles('dyslexic-font', `
                    @font-face {
                        font-family: 'OpenDyslexic';
                        src: url('https://cdn.jsdelivr.net/npm/open-dyslexic@1.0.2/OpenDyslexic-Regular.otf');
                    }
                    body, p, span, div, h1, h2, h3, h4, h5, h6, a, li {
                        font-family: 'OpenDyslexic', sans-serif !important;
                    }
                `);
            } else {
                this.removeGlobalStyles('dyslexic-font');
            }

            // High Cursor
            if (s.high_cursor) {
                root.style.cursor = 'url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'64\' height=\'64\' viewBox=\'0 0 64 64\'%3E%3Cpath d=\'M0 0 L32 32 L16 32 L0 48 Z\' fill=\'black\' stroke=\'white\' stroke-width=\'2\'/%3E%3C/svg%3E"), auto';
            } else {
                root.style.cursor = 'auto';
            }

            // Highlight Links
            if (s.highlight_links) {
                this.injectGlobalStyles('highlight-links', `
                    a {
                        background-color: yellow !important;
                        color: black !important;
                        text-decoration: underline !important;
                        font-weight: bold !important;
                    }
                `);
            } else {
                this.removeGlobalStyles('highlight-links');
            }

            // Stop Animations
            if (s.stop_animations) {
                this.injectGlobalStyles('stop-animations', `
                    *, *::before, *::after {
                        animation-duration: 0.01ms !important;
                        animation-iteration-count: 1 !important;
                        transition-duration: 0.01ms !important;
                        scroll-behavior: auto !important;
                    }
                `);
            } else {
                this.removeGlobalStyles('stop-animations');
            }

            // Highlight Titles
            if (s.highlight_titles) {
                this.injectGlobalStyles('highlight-titles', `
                    h1, h2, h3, h4, h5, h6 {
                        background-color: rgba(255, 255, 0, 0.2) !important;
                        border-left: 5px solid var(--primary-color) !important;
                        padding-left: 10px !important;
                    }
                `);
            } else {
                this.removeGlobalStyles('highlight-titles');
            }

            // Text Position (Align Left)
            if (s.text_position) {
                this.injectGlobalStyles('text-position', `
                    body, p, div, h1, h2, h3, h4, h5, h6, li {
                        text-align: left !important;
                    }
                `);
            } else {
                this.removeGlobalStyles('text-position');
            }

            // Page Structure
            if (s.page_structure) {
                this.injectGlobalStyles('page-structure', `
                    h1, h2, h3, h4, h5, h6, header, footer, main, nav, section, article {
                        outline: 2px dashed var(--primary-color) !important;
                        outline-offset: 2px !important;
                        position: relative !important;
                    }
                    h1::before, h2::before, h3::before, h4::before, h5::before, h6::before {
                        content: attr(tagName);
                        position: absolute;
                        top: -20px;
                        left: 0;
                        background: var(--primary-color);
                        color: white;
                        font-size: 10px;
                        padding: 2px 4px;
                        z-index: 1000;
                    }
                `);
                document.querySelectorAll('h1, h2, h3, h4, h5, h6').forEach(el => {
                    el.setAttribute('tagName', el.tagName);
                });
            } else {
                this.removeGlobalStyles('page-structure');
                document.querySelectorAll('[tagName]').forEach(el => {
                    el.removeAttribute('tagName');
                });
            }

            // Keyboard Navigation
            if (s.keyboard_navigation) {
                this.injectGlobalStyles('keyboard-navigation', `
                    *:focus {
                        outline: 4px solid var(--primary-color) !important;
                        outline-offset: 4px !important;
                        box-shadow: 0 0 0 8px rgba(255, 255, 255, 0.8) !important;
                    }
                `);
            } else {
                this.removeGlobalStyles('keyboard-navigation');
            }

            // Voice Navigation
            if (s.voice_navigation) {
                this.enableVoiceNavigation();
            } else {
                this.disableVoiceNavigation();
            }

            // Hide Images
            if (s.hide_images) {
                this.injectGlobalStyles('hide-images', `
                    img, figure, video, [style*="background-image"] {
                        visibility: hidden !important;
                        opacity: 0 !important;
                    }
                `);
            } else {
                this.removeGlobalStyles('hide-images');
            }

            // Screen Reader
            if (s.screen_reader) {
                this.enableScreenReader();
            } else {
                this.disableScreenReader();
            }

            // Text Reader
            if (s.text_reader) {
                this.enableTextReader();
            } else {
                this.disableTextReader();
            }

            // Reading Guide
            if (s.reading_guide) {
                this.enableReadingGuide();
            } else {
                this.disableReadingGuide();
            }

            // Line Height
            if (s.line_height && s.line_height !== 1.5) {
                this.injectGlobalStyles('line-height', `
                    body, p, span, div, h1, h2, h3, h4, h5, h6, a, li {
                        line-height: ${s.line_height} !important;
                    }
                `);
            } else {
                this.removeGlobalStyles('line-height');
            }

            // Letter Spacing
            if (s.letter_spacing && s.letter_spacing !== 0) {
                this.injectGlobalStyles('letter-spacing', `
                    body, p, span, div, h1, h2, h3, h4, h5, h6, a, li {
                        letter-spacing: ${s.letter_spacing}px !important;
                    }
                `);
            } else {
                this.removeGlobalStyles('letter-spacing');
            }

            // Dictionary
            if (s.dictionary) {
                this.enableDictionary();
            } else {
                this.disableDictionary();
            }

            // Tooltips
            if (s.tooltips) {
                this.enableTooltips();
            } else {
                this.disableTooltips();
            }
        }

        enableScreenReader() {
            if (this._screenReaderFocus) return;
            // Screen reader usually means announcing elements on focus
            this._screenReaderFocus = (e) => {
                const text = e.target.innerText || e.target.alt || e.target.ariaLabel || e.target.title;
                if (text && text.trim().length > 0) {
                    this.speak(text);
                }
            };
            document.addEventListener('focusin', this._screenReaderFocus);
        }

        disableScreenReader() {
            if (this._screenReaderFocus) {
                document.removeEventListener('focusin', this._screenReaderFocus);
                this._screenReaderFocus = null;
            }
            window.speechSynthesis.cancel();
        }

        enableTextReader() {
            if (this._textReaderClick) return;
            this._textReaderClick = (e) => {
                const text = e.target.innerText || e.target.alt || e.target.title;
                if (text && text.trim().length > 0) {
                    this.speak(text);
                }
            };
            document.addEventListener('click', this._textReaderClick);
        }

        disableTextReader() {
            if (this._textReaderClick) {
                document.removeEventListener('click', this._textReaderClick);
                this._textReaderClick = null;
            }
            window.speechSynthesis.cancel();
        }

        enableVoiceNavigation() {
            if (this.recognition) return;
            if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
                console.warn('Speech recognition not supported');
                return;
            }
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            this.recognition = new SpeechRecognition();
            this.recognition.continuous = true;
            this.recognition.interimResults = false;
            this.recognition.onresult = (event) => {
                const command = event.results[event.results.length - 1][0].transcript.toLowerCase();
                if (command.includes('scroll down')) window.scrollBy(0, 300);
                if (command.includes('scroll up')) window.scrollBy(0, -300);
                if (command.includes('open accessibility')) this.toggleWidget();
            };
            this.recognition.start();
        }

        disableVoiceNavigation() {
            if (this.recognition) {
                this.recognition.stop();
                this.recognition = null;
            }
        }

        speak(text) {
            window.speechSynthesis.cancel();
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = this.state.websiteLocale;
            window.speechSynthesis.speak(utterance);
        }

        enableReadingGuide() {
            if (this._readingGuideMove) return;
            let guide = document.getElementById('agentoom-reading-guide');
            if (!guide) {
                guide = document.createElement('div');
                guide.id = 'agentoom-reading-guide';
                guide.style.cssText = 'position: fixed; left: 0; width: 100%; height: 50px; background: rgba(0,0,0,0.1); border-top: 2px solid black; border-bottom: 2px solid black; pointer-events: none; z-index: 999998; display: none;';
                document.body.appendChild(guide);
            }
            guide.style.display = 'block';
            this._readingGuideMove = (e) => {
                guide.style.top = (e.clientY - 25) + 'px';
            };
            window.addEventListener('mousemove', this._readingGuideMove);
        }

        disableReadingGuide() {
            const guide = document.getElementById('agentoom-reading-guide');
            if (guide) guide.style.display = 'none';
            if (this._readingGuideMove) {
                window.removeEventListener('mousemove', this._readingGuideMove);
                this._readingGuideMove = null;
            }
        }

        enableDictionary() {
            if (this._dictionaryMouseUp) return;
            this._dictionaryMouseUp = async (e) => {
                const selection = window.getSelection().toString().trim();
                if (selection.length > 0 && selection.split(' ').length === 1) {
                    const word = selection.toLowerCase().replace(/[^\p{L}\-']/gu, '');
                    if (word.length > 0) {
                        this.showDictionaryPopup(word, e.pageX, e.pageY);
                    }
                }
            };
            document.addEventListener('mouseup', this._dictionaryMouseUp);
        }

        disableDictionary() {
            if (this._dictionaryMouseUp) {
                document.removeEventListener('mouseup', this._dictionaryMouseUp);
                this._dictionaryMouseUp = null;
            }
            const popup = document.getElementById('agentoom-dictionary-popup');
            if (popup) popup.remove();
        }

        async showDictionaryPopup(word, x, y) {
            let popup = document.getElementById('agentoom-dictionary-popup');
            if (!popup) {
                popup = document.createElement('div');
                popup.id = 'agentoom-dictionary-popup';
                popup.style.cssText = 'position: absolute; background: white; border: 1px solid #ccc; border-radius: 8px; padding: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 1000000; max-width: 250px; font-size: 14px; color: #333;';
                document.body.appendChild(popup);
            }
            popup.style.display = 'block';
            popup.style.left = x + 'px';
            popup.style.top = (y + 10) + 'px';
            popup.innerHTML = '<i>Loading definition...</i>';

            try {
                const response = await fetch(`https://dictionary.agentoom.com/api/v1/define?word=${encodeURIComponent(word)}&lang=${this.state.websiteLocale}`);
                if (!response.ok) throw new Error('Not found');
                const data = await response.json();

                let definition = null;
                if (data.entries && data.entries.length > 0) {
                    for (const entry of data.entries) {
                        if (entry.senses && entry.senses.length > 0) {
                            for (const sense of entry.senses) {
                                definition = sense.glosses ? sense.glosses[0] : sense.definition;
                                if (definition) break;
                            }
                        }
                        if (definition) break;
                    }
                }

                if (definition) {
                    popup.innerHTML = `<strong>${word}</strong>: ${definition}`;
                } else {
                    popup.innerHTML = `<strong>${word}</strong>: Definition not found.`;
                }
            } catch (err) {
                popup.innerHTML = `<strong>${word}</strong>: Definition not found.`;
            }

            const closePopup = (e) => {
                if (!popup.contains(e.target)) {
                    popup.style.display = 'none';
                    document.removeEventListener('mousedown', closePopup);
                }
            };
            setTimeout(() => document.addEventListener('mousedown', closePopup), 10);
        }

        enableTooltips() {
            if (this._tooltipMouseOver) return;
            this._tooltipMouseOver = (e) => {
                const target = e.target.closest('[title], [alt]');
                if (target) {
                    const text = target.getAttribute('title') || target.getAttribute('alt');
                    if (text) {
                        this.showTooltip(text, e.pageX, e.pageY);
                    }
                }
            };
            this._tooltipMouseOut = () => {
                const tooltip = document.getElementById('agentoom-acc-tooltip');
                if (tooltip) tooltip.style.display = 'none';
            };
            document.addEventListener('mouseover', this._tooltipMouseOver);
            document.addEventListener('mouseout', this._tooltipMouseOut);
        }

        disableTooltips() {
            if (this._tooltipMouseOver) {
                document.removeEventListener('mouseover', this._tooltipMouseOver);
                document.removeEventListener('mouseout', this._tooltipMouseOut);
                this._tooltipMouseOver = null;
                this._tooltipMouseOut = null;
            }
            const tooltip = document.getElementById('agentoom-acc-tooltip');
            if (tooltip) tooltip.remove();
        }

        showTooltip(text, x, y) {
            let tooltip = document.getElementById('agentoom-acc-tooltip');
            if (!tooltip) {
                tooltip = document.createElement('div');
                tooltip.id = 'agentoom-acc-tooltip';
                tooltip.style.cssText = 'position: absolute; background: #333; color: white; padding: 5px 10px; border-radius: 4px; font-size: 12px; z-index: 1000001; pointer-events: none; max-width: 200px;';
                document.body.appendChild(tooltip);
            }
            tooltip.style.display = 'block';
            tooltip.innerText = text;
            tooltip.style.left = (x + 10) + 'px';
            tooltip.style.top = (y + 10) + 'px';
        }

        injectGlobalStyles(id, css) {
            let style = document.getElementById(`agentoom-acc-style-${id}`);
            if (!style) {
                style = document.createElement('style');
                style.id = `agentoom-acc-style-${id}`;
                document.head.appendChild(style);
            }
            style.textContent = css;
        }

        removeGlobalStyles(id) {
            const style = document.getElementById(`agentoom-acc-style-${id}`);
            if (style) style.remove();
        }

        toggleWidget() {
            this.state.isOpen = !this.state.isOpen;
            this.render();
        }

        toggleSection(section) {
            this.state.expandedSections[section] = !this.state.expandedSections[section];
            this.render();
        }

        updateSetting(key, value) {
            this.state.activeSettings[key] = value;
            localStorage.setItem(`agentoom_acc_${config.slug}`, JSON.stringify(this.state.activeSettings));
            this.applySettings();
            this.render();
        }

        changeLocale(locale) {
            this.state.locale = locale;
            localStorage.setItem('agentoom_acc_locale', locale);
            this.render();
        }

        getIcon(name) {
            const icons = {
                'screen-reader': `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 8a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8z"/><path d="M2 14h20"/><path d="M6 10h12"/><path d="M6 18h12"/><path d="M10 10v4"/><path d="M14 10v4"/></svg>`,
                'keyboard': `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M6 8h.01"/><path d="M10 8h.01"/><path d="M14 8h.01"/><path d="M18 8h.01"/><path d="M6 12h.01"/><path d="M10 12h.01"/><path d="M14 12h.01"/><path d="M18 12h.01"/><path d="M7 16h10"/></svg>`,
                'voice': `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="22"/><line x1="8" y1="22" x2="16" y2="22"/></svg>`,
                'text-reader': `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>`,
                'contrast': `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 18a6 6 0 0 0 0-12v12z"/></svg>`,
                'saturation': `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 22 4.44-4.44a7 7 0 1 0-8.88 0L12 22Z"/></svg>`,
                'monochrome': `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M12 22a10 10 0 0 0 0-20"/><path d="M12 18a6 6 0 0 0 0-12"/><path d="M12 14a2 2 0 0 0 0-4"/></svg>`,
                'text-position': `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="21" y1="6" x2="3" y2="6"/><line x1="15" y1="12" x2="3" y2="12"/><line x1="17" y1="18" x2="3" y2="18"/></svg>`,
                'link': `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>`,
                'type': `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>`,
                'font': `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7V4h16v3"/><path d="M9 20h6"/><path d="M12 4v16"/><path d="M11 13h2"/></svg>`,
                'cursor': `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 3 7.07 16.97 2.51-7.39 7.39-2.51L3 3z"/><path d="m13 13 6 6"/></svg>`,
                'stop': `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>`,
                'aid': `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>`,
                'structure': `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h18v18H3z"/><path d="M3 9h18"/><path d="M3 15h18"/><path d="M9 3v18"/></svg>`,
                'dictionary': `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><path d="M9 7h6"/><path d="M9 11h6"/></svg>`,
                'image-off': `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="2" y1="2" x2="22" y2="22"/><path d="M10.41 10.41 4.67 16.14a2 2 0 0 0 2.83 2.83l5.73-5.74"/><path d="m14.59 14.59 5.74 5.74a2 2 0 0 0 2.83-2.83l-5.74-5.73"/><path d="M9 3h10a2 2 0 0 1 2 2v10"/><path d="M3 13v2a2 2 0 0 0 2 2h2"/></svg>`,
                'tooltip': `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>`,
                'chevron-down': `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>`,
                'accessibility': `<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="1" /><path d="m9 20 3-6 3 6" /><path d="m6 8 6 2 6-2" /><path d="M12 10v4" /></svg>`
            };
            return icons[name] || '';
        }

        render() {
            const s = this.state.activeSettings;
            const isOpen = this.state.isOpen;
            const expanded = this.state.expandedSections;
            const panelContent = this.shadowRoot.querySelector('.panel-content');
            const scrollPos = panelContent ? panelContent.scrollTop : 0;

            this.shadowRoot.innerHTML = `
                <style>
                    :host {
                        --primary-color: ${this.state.settings.primary_color || '#2563eb'};
                        --bg-color: ${this.state.settings.bg_color || '#ffffff'};
                        --header-bg: #374151;
                        --text-color: ${this.state.settings.text_color || '#1f2937'};
                        --border-color: rgba(0, 0, 0, 0.1);
                        position: fixed;
                        ${this.getPositionStyles()}
                        z-index: 999999;
                        font-family: system-ui, -apple-system, sans-serif;
                    }
                    @import url('https://fonts.bunny.net/css?family=inter:400,500,600,700');
                    /* Adjust border color based on background brightness */
                    @supports (color: color-mix(in srgb, var(--bg-color), white)) {
                        :host {
                            --border-color: color-mix(in srgb, var(--text-color) 20%, transparent);
                        }
                    }
                    .trigger {
                        width: 60px;
                        height: 60px;
                        border-radius: 50%;
                        background: var(--primary-color);
                        color: white;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        cursor: pointer;
                        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                        transition: transform 0.2s;
                    }
                    .trigger:hover {
                        transform: scale(1.1);
                    }
                    .panel {
                        display: ${isOpen ? 'flex' : 'none'};
                        flex-direction: column;
                        position: absolute;
                        ${this.getPanelPositionStyles()}
                        width: 400px;
                        height: 600px;
                        background: var(--bg-color);
                        border-radius: 12px;
                        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                        overflow: hidden;
                        color: var(--text-color);
                        border: 1px solid var(--border-color);
                    }
                    .panel-header {
                        background: var(--header-bg);
                        color: white;
                        padding: 16px;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                    }
                    .panel-header span {
                        font-weight: 600;
                        font-size: 1.1rem;
                    }
                    .close-btn {
                        cursor: pointer;
                        padding: 4px;
                        display: flex;
                    }
                    .panel-content {
                        flex-grow: 1;
                        overflow-y: auto;
                        background: var(--bg-color);
                    }
                    .accordion-item {
                        border-bottom: 1px solid var(--border-color);
                        background: var(--bg-color);
                    }
                    .accordion-header {
                        padding: 12px 16px;
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        cursor: pointer;
                        font-weight: 600;
                        font-size: 0.95rem;
                    }
                    .accordion-header-left {
                        display: flex;
                        align-items: center;
                        gap: 12px;
                    }
                    .accordion-content {
                        padding: 16px;
                        display: none;
                    }
                    .accordion-content.expanded {
                        display: block;
                    }
                    .grid-container {
                        display: grid;
                        grid-template-columns: repeat(3, 1fr);
                        gap: 12px;
                    }
                    .big-button {
                        background: var(--bg-color);
                        border: 1px solid var(--border-color);
                        border-radius: 8px;
                        padding: 16px 8px;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        justify-content: center;
                        text-align: center;
                        cursor: pointer;
                        transition: all 0.2s;
                        gap: 8px;
                    }
                    .big-button:hover {
                        border-color: var(--primary-color);
                        background: var(--primary-color);
                        color: white;
                    }
                    .big-button:hover svg {
                        color: white;
                    }
                    .big-button.active {
                        border-color: var(--primary-color);
                        background: rgba(37, 99, 235, 0.1);
                        box-shadow: inset 0 0 0 1px var(--primary-color);
                    }
                    @supports (color: color-mix(in srgb, white, black)) {
                        .big-button.active {
                            background: color-mix(in srgb, var(--primary-color) 10%, transparent);
                        }
                    }
                    .big-button svg {
                        color: var(--text-color);
                        opacity: 0.7;
                    }
                    .big-button.active svg {
                        color: var(--primary-color);
                    }
                    .big-button-text {
                        font-size: 0.75rem;
                        font-weight: 500;
                        line-height: 1.2;
                    }
                    .language-select {
                        width: 100%;
                        padding: 8px;
                        border-radius: 6px;
                        border: 1px solid var(--border-color);
                        background: var(--bg-color);
                        color: var(--text-color);
                        font-size: 0.9rem;
                    }
                    .panel-footer {
                        padding: 16px;
                        background: var(--bg-color);
                        border-top: 1px solid var(--border-color);
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                    }
                    .logo-placeholder {
                        color: var(--primary-color);
                        display: flex;
                        align-items: center;
                        gap: 6px;
                    }
                    .brand-text {
                        background: linear-gradient(to right, #00fbff, #ad33ff);
                        -webkit-background-clip: text;
                        -webkit-text-fill-color: transparent;
                        font-family: 'Inter', sans-serif;
                        font-weight: 700;
                        letter-spacing: -0.025em;
                        font-size: 0.9rem;
                    }
                    .reset-btn {
                        padding: 8px 16px;
                        background: var(--bg-color);
                        color: var(--text-color);
                        border: 1px solid var(--border-color);
                        border-radius: 6px;
                        cursor: pointer;
                        font-size: 0.85rem;
                        font-weight: 600;
                        display: flex;
                        align-items: center;
                        gap: 6px;
                    }
                    .reset-btn:hover {
                        background: var(--primary-color);
                        color: white;
                    }
                    .dot-indicators {
                        display: flex;
                        gap: 4px;
                        margin-top: 4px;
                    }
                    .dot {
                        width: 6px;
                        height: 6px;
                        border-radius: 50%;
                        background: #d1d5db;
                    }
                    .dot.filled {
                        background: var(--primary-color);
                    }
                    @media (max-width: 480px) {
                        .panel {
                            width: calc(100vw - 40px);
                            height: calc(100vh - 120px);
                            max-height: 600px;
                        }
                        .grid-container {
                            grid-template-columns: repeat(2, 1fr);
                        }
                        .close-btn {
                            padding: 12px;
                            margin: -8px;
                        }
                    }
                </style>
                <div class="trigger" id="acc-trigger">
                    ${this.state.icon_trigger
                        ? `<img src="${this.state.icon_trigger}" alt="Accessibility" style="width: 32px; height: 32px;" />`
                        : this.getIcon('accessibility')
                    }
                </div>
                <div class="panel">
                    <div class="panel-header">
                        <span>${this.t('title')}</span>
                        <div class="close-btn" id="acc-close">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </div>
                    </div>

                    <div class="panel-content">
                        <!-- Language Section -->
                        <div class="accordion-item">
                            <div class="accordion-header" id="header-language">
                                <div class="accordion-header-left">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                    <span>${this.t('language')}</span>
                                </div>
                                <div style="transform: ${expanded.language ? 'rotate(180deg)' : 'none'}">${this.getIcon('chevron-down')}</div>
                            </div>
                            <div class="accordion-content ${expanded.language ? 'expanded' : ''}">
                                <select class="language-select" id="lang-select">
                                    ${this.state.availableLanguages.map(lang => `
                                        <option value="${lang.language_code}" ${this.state.locale === lang.language_code ? 'selected' : ''}>
                                            ${lang.language}
                                        </option>
                                    `).join('')}
                                </select>
                            </div>
                        </div>

                        <!-- Profiles Section -->
                        <div class="accordion-item">
                            <div class="accordion-header" id="header-profiles">
                                <div class="accordion-header-left">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    <span>${this.t('profiles')}</span>
                                </div>
                                <div style="transform: ${expanded.profiles ? 'rotate(180deg)' : 'none'}">${this.getIcon('chevron-down')}</div>
                            </div>
                            <div class="accordion-content ${expanded.profiles ? 'expanded' : ''}">
                                <!-- Profiles could go here -->
                                <p style="font-size: 0.8rem; color: #6b7280; text-align: center;">Standard profiles coming soon</p>
                            </div>
                        </div>

                        <!-- Main Options Section -->
                        <div class="accordion-item">
                            <div class="accordion-header" id="header-options">
                                <div class="accordion-header-left">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                                    <span>${this.t('main_options')}</span>
                                </div>
                                <div style="transform: ${expanded.options ? 'rotate(180deg)' : 'none'}">${this.getIcon('chevron-down')}</div>
                            </div>
                            <div class="accordion-content ${expanded.options ? 'expanded' : ''}">
                                <div class="grid-container">
                                    ${this.state.settings.screen_reader ? this.renderBigButton('screen_reader', 'screen-reader', s.screen_reader) : ''}
                                    ${this.state.settings.keyboard_navigation ? this.renderBigButton('keyboard_navigation', 'keyboard', s.keyboard_navigation) : ''}
                                    ${this.state.settings.voice_navigation ? this.renderBigButton('voice_navigation', 'voice', s.voice_navigation) : ''}
                                    ${this.state.settings.text_reader ? this.renderBigButton('text_reader', 'text-reader', s.text_reader) : ''}
                                    ${this.state.settings.contrast ? this.renderBigButton('contrast', 'contrast', s.contrast) : ''}
                                    ${this.state.settings.saturation ? this.renderBigButton('saturation', 'saturation', s.saturation) : ''}
                                    ${this.state.settings.monochrome ? this.renderBigButton('monochrome', 'monochrome', s.monochrome) : ''}
                                    ${this.state.settings.text_position ? this.renderBigButton('text_position', 'text-position', s.text_position) : ''}
                                    ${this.state.settings.highlight_links ? this.renderBigButton('highlight_links', 'link', s.highlight_links) : ''}
                                    ${this.state.settings.highlight_titles ? this.renderBigButton('highlight_titles', 'type', s.highlight_titles) : ''}
                                    ${this.state.settings.dyslexic_font ? this.renderBigButton('readable_fonts', 'font', s.dyslexic_font) : ''}
                                    ${this.state.settings.high_cursor ? this.renderBigButton('big_cursor', 'cursor', s.high_cursor) : ''}
                                    ${this.state.settings.stop_animations ? this.renderBigButton('stop_animations', 'stop', s.stop_animations) : ''}
                                    ${this.state.settings.reading_guide ? this.renderBigButton('reading_aid', 'aid', s.reading_guide) : ''}
                                    ${this.state.settings.page_structure ? this.renderBigButton('page_structure', 'structure', s.page_structure) : ''}
                                    ${this.state.settings.dictionary ? this.renderBigButton('dictionary', 'dictionary', s.dictionary) : ''}
                                    ${this.state.settings.hide_images ? this.renderBigButton('hide_images', 'image-off', s.hide_images) : ''}
                                    ${this.state.settings.tooltips ? this.renderBigButton('tooltips', 'tooltip', s.tooltips) : ''}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel-footer">
                        <div class="logo-placeholder">
                            <a href="https://agentoom.com"
                               style="text-decoration: none; display: flex; align-items: center; gap: 6px;"
                               target="_blank" rel="noopener noreferrer">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" fill="none" style="width: 18px; height: 18px;">
                                    <defs>
                                        <linearGradient id="logo-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" style="stop-color:#00fbff;stop-opacity:1" />
                                            <stop offset="100%" style="stop-color:#ad33ff;stop-opacity:1" />
                                        </linearGradient>
                                    </defs>
                                    <path d="M16 5L26 25H21L16 15L11 25H6L16 5Z" fill="url(#logo-gradient)"></path>
                                </svg>
                                <span class="brand-text">Agentoom</span>
                            </a>
                        </div>
                        <button class="reset-btn" id="acc-reset">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                            ${this.t('reset_all')}
                        </button>
                    </div>
                </div>
            `;

            this.shadowRoot.getElementById('acc-trigger').onclick = () => this.toggleWidget();
            this.shadowRoot.getElementById('acc-close').onclick = () => this.toggleWidget();

            this.shadowRoot.getElementById('header-language').onclick = () => this.toggleSection('language');
            this.shadowRoot.getElementById('header-profiles').onclick = () => this.toggleSection('profiles');
            this.shadowRoot.getElementById('header-options').onclick = () => this.toggleSection('options');

            this.shadowRoot.getElementById('lang-select').onchange = (e) => this.changeLocale(e.target.value);

            // Bind buttons
            this.bindButton('btn-screen_reader', 'screen_reader');
            this.bindButton('btn-keyboard_navigation', 'keyboard_navigation');
            this.bindButton('btn-voice_navigation', 'voice_navigation');
            this.bindButton('btn-text_reader', 'text_reader');
            this.bindButton('btn-contrast', 'contrast');
            this.bindButton('btn-saturation', 'saturation');
            this.bindButton('btn-monochrome', 'monochrome');
            this.bindButton('btn-text_position', 'text_position');
            this.bindButton('btn-highlight_links', 'highlight_links');
            this.bindButton('btn-highlight_titles', 'highlight_titles');
            this.bindButton('btn-readable_fonts', 'dyslexic_font');
            this.bindButton('btn-big_cursor', 'high_cursor');
            this.bindButton('btn-stop_animations', 'stop_animations');
            this.bindButton('btn-reading_aid', 'reading_guide');
            this.bindButton('btn-page_structure', 'page_structure');
            this.bindButton('btn-dictionary', 'dictionary');
            this.bindButton('btn-hide_images', 'hide_images');
            this.bindButton('btn-tooltips', 'tooltips');

            this.shadowRoot.getElementById('acc-reset').onclick = () => {
                this.state.activeSettings = this.getDefaultSettings(this.state.settings);
                localStorage.removeItem(`agentoom_acc_${config.slug}`);
                this.applySettings();
                this.render();
            };

            if (panelContent) {
                const newPanelContent = this.shadowRoot.querySelector('.panel-content');
                if (newPanelContent) {
                    newPanelContent.scrollTop = scrollPos;
                }
            }
        }

        renderBigButton(key, icon, isActive) {
            return `
                <div class="big-button ${isActive ? 'active' : ''}" id="btn-${key}">
                    ${this.getIcon(icon)}
                    <span class="big-button-text">${this.t(key)}</span>
                </div>
            `;
        }

        bindButton(id, settingKey) {
            const btn = this.shadowRoot.getElementById(id);
            if (btn) {
                btn.onclick = () => this.updateSetting(settingKey, !this.state.activeSettings[settingKey]);
            }
        }
    }

    customElements.define('agentoom-accessibility-widget', AgentoomAccessibilityWidget);
    const widget = document.createElement('agentoom-accessibility-widget');
    document.body.appendChild(widget);
})();
