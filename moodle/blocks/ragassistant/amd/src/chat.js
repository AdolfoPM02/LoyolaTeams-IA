define(['core/ajax', 'core/notification', 'core/str'], function(Ajax, Notification, Str) {

    const init = function(config) {
        const root = document.querySelector('[data-region="ragassistant"]');
        if (!root) { return; }

        const input       = root.querySelector('[data-action="question"]');
        const askBtn      = root.querySelector('[data-action="ask"]');
        const spinner     = root.querySelector('.ragassistant-spinner');
        const answerWrap  = root.querySelector('[data-region="answer-wrap"]');
        const answerEl    = root.querySelector('[data-region="answer"]');
        const sourcesWrap = root.querySelector('[data-region="sources-wrap"]');
        const sourcesList = root.querySelector('[data-region="sources"]');
        const warnWrap    = root.querySelector('[data-region="warnings-wrap"]');
        const warnList    = root.querySelector('[data-region="warnings"]');

        if (!input || !askBtn) { return; }

        askBtn.addEventListener('click', function() {
            const question = input.value.trim();
            if (!question) { return; }

            setLoading(true);
            clearOutput();

            Ajax.call([{
                methodname: 'block_ragassistant_ask',
                args: {courseid: config.courseid, question: question, cmid: config.cmid || 0}
            }])[0].then(function(response) {
                renderResponse(response);
                setLoading(false);
                return response;
            }).catch(function(error) {
                setLoading(false);
                showStatusMessage('errorapi');
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
                setText(answerEl, 'thinking');
                answerWrap.classList.remove('d-none');
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
        }

        /**
         * Renderiza según el status del contrato:
         * answered | abstained | error | invalid_request | degraded.
         */
        function renderResponse(response) {
            const status = response.status || 'error';

            if (status === 'abstained') {
                answerWrap.classList.remove('d-none');
                if (response.answer) {
                    answerEl.textContent = response.answer;
                } else {
                    setText(answerEl, 'nocontext');
                }
                renderWarnings(response.warnings);
                return;
            }

            if (status !== 'answered') {
                var key = status === 'degraded' ? 'errordegraded'
                    : (status === 'invalid_request' ? 'errorinvalid' : 'errorapi');
                showStatusMessage(key);
                renderWarnings(response.warnings);
                return;
            }

            // status === 'answered'
            answerWrap.classList.remove('d-none');
            answerEl.textContent = response.answer || '';

            // El best_score del backend es interno (evaluación/depuración) y NO
            // se muestra al usuario: podría malinterpretarse como probabilidad
            // de veracidad.

            renderSources(response.sources);
            renderWarnings(response.warnings);
        }

        /**
         * Renderiza las fuentes numeradas [1], [2], [3]... en el mismo orden
         * que devuelve /ask, para que coincidan con las citas inline de la
         * respuesta. No se muestra el source_uri crudo ni rutas internas.
         */
        function renderSources(sources) {
            if (!sources || !sources.length || !sourcesWrap) { return; }
            sourcesWrap.classList.remove('d-none');
            sourcesList.innerHTML = '';
            sources.forEach(function(src, i) {
                var label = src.document_title || src.chunk_id || 'Fuente';
                if (src.section) { label += ' — ' + src.section; }
                if (src.page)    { label += ' (pág. ' + src.page + ')'; }

                var li = document.createElement('li');
                // Número de cita visible, fuera del enlace: [1] Título — sección.
                li.appendChild(document.createTextNode('[' + (i + 1) + '] '));

                if (src.source_uri && /^https?:\/\//i.test(src.source_uri)) {
                    var a = document.createElement('a');
                    a.href = src.source_uri;
                    a.target = '_blank';
                    a.rel = 'noopener noreferrer';
                    a.textContent = label;
                    li.appendChild(a);
                } else {
                    li.appendChild(document.createTextNode(label));
                }
                sourcesList.appendChild(li);
            });
        }

        function renderWarnings(warnings) {
            if (!warnings || !warnings.length || !warnWrap) { return; }
            warnWrap.classList.remove('d-none');
            warnList.innerHTML = '';
            warnings.forEach(function(w) {
                var li = document.createElement('li');
                li.textContent = '⚠ ' + w;
                warnList.appendChild(li);
            });
        }

        function showStatusMessage(strkey) {
            answerWrap.classList.remove('d-none');
            setText(answerEl, strkey);
        }

        function setText(el, strkey) {
            if (!el) { return; }
            Str.get_string(strkey, 'block_ragassistant').then(function(msg) {
                el.textContent = msg;
                return msg;
            }).catch(function() {
                return null;
            });
        }
    };

    return {init: init};
});
