define(['core/ajax', 'core/notification', 'core/str'], function(Ajax, Notification, Str) {

    const init = function(config) {
        const root = document.querySelector('[data-region="ragassistant"]');
        if (!root) { return; }

        const input       = root.querySelector('[data-action="question"]');
        const askBtn      = root.querySelector('[data-action="ask"]');
        const spinner     = root.querySelector('.ragassistant-spinner');
        const answerWrap  = root.querySelector('[data-region="answer-wrap"]');
        const answerEl    = root.querySelector('[data-region="answer"]');
        const confEl      = root.querySelector('[data-region="confidence"]');
        const sourcesWrap = root.querySelector('[data-region="sources-wrap"]');
        const sourcesList = root.querySelector('[data-region="sources"]');
        const warnWrap    = root.querySelector('[data-region="warnings-wrap"]');
        const warnList    = root.querySelector('[data-region="warnings"]');

        askBtn.addEventListener('click', function() {
            const question = input.value.trim();
            if (!question) { return; }

            setLoading(true);
            clearOutput();

            Ajax.call([{
                methodname: 'block_ragassistant_ask',
                args: { courseid: config.courseid, question: question, cmid: config.cmid || 0 }
            }])[0].then(function(response) {
                renderAnswer(response);
                setLoading(false);
                return response;
            }).catch(function(error) {
                setLoading(false);
                Str.get_string('errorapi', 'block_ragassistant').then(function(msg) {
                    answerWrap.classList.remove('d-none');
                    answerEl.textContent = msg;
                });
                Notification.exception(error);
            });
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); askBtn.click(); }
        });

        function setLoading(active) {
            askBtn.disabled = active;
            if (active) {
                spinner.classList.remove('d-none');
                Str.get_string('thinking', 'block_ragassistant').then(function(msg) {
                    answerWrap.classList.remove('d-none');
                    answerEl.textContent = msg;
                });
            } else {
                spinner.classList.add('d-none');
            }
        }

        function clearOutput() {
            [answerWrap, sourcesWrap, warnWrap].forEach(function(el) {
                if (el) { el.classList.add('d-none'); }
            });
            if (sourcesList) { sourcesList.innerHTML = ''; }
            if (warnList)    { warnList.innerHTML = ''; }
            if (confEl)      { confEl.textContent = ''; }
        }

        function renderAnswer(response) {
            answerWrap.classList.remove('d-none');
            answerEl.textContent = response.answer || '';

            if (response.confidence && confEl) {
                confEl.textContent = 'Confianza: ' + Math.round(response.confidence * 100) + '%';
                confEl.classList.remove('d-none');
            }

            if (response.sources && response.sources.length > 0 && sourcesWrap) {
                sourcesWrap.classList.remove('d-none');
                sourcesList.innerHTML = response.sources.map(function(src) {
                    var label = src.title || 'Fuente';
                    if (src.page)  { label += ' — pág. ' + src.page; }
                    if (src.score) { label += ' (' + Math.round(src.score * 100) + '%)'; }
                    return src.url
                        ? '<li><a href="' + src.url + '" target="_blank">' + label + '</a></li>'
                        : '<li>' + label + '</li>';
                }).join('');
            }

            if (response.warnings && response.warnings.length > 0 && warnWrap) {
                warnWrap.classList.remove('d-none');
                warnList.innerHTML = response.warnings.map(function(w) {
                    return '<li>⚠ ' + w + '</li>';
                }).join('');
            }
        }
    };

    return { init: init };
});