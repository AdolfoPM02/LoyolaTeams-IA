define(['core/ajax', 'core/notification', 'core/str'], function(Ajax, Notification, Str) {

    const init = function(config) {
        const root = document.querySelector('[data-region="ragindexer"]');
        if (!root) { return; }

        const indexBtn   = root.querySelector('[data-action="index-course"]');
        const forceCb     = root.querySelector('[data-action="force"]');
        const spinner     = root.querySelector('.ragindexer-spinner');
        const statusEl    = root.querySelector('[data-region="index-status"]');
        const partialEl   = root.querySelector('[data-region="partial"]');
        const resultEl    = root.querySelector('[data-region="result"]');
        const errorsWrap  = root.querySelector('[data-region="errors-wrap"]');
        const errorsList  = root.querySelector('[data-region="errors"]');

        if (!indexBtn) { return; }

        let running = false;

        indexBtn.addEventListener('click', function() {
            if (running) { return; }
            running = true;
            indexBtn.disabled = true;
            if (spinner) { spinner.classList.remove('d-none'); }
            clearOutput();
            setStatus('indexing');

            Ajax.call([{
                methodname: 'block_ragindexer_index_course',
                args: {courseid: config.courseid, force: forceCb ? forceCb.checked : false}
            }])[0].then(function(response) {
                render(response);
                done();
                return response;
            }).catch(function(error) {
                setStatus('indexerror');
                done();
                Notification.exception(error);
            });
        });

        function done() {
            running = false;
            indexBtn.disabled = false;
            if (spinner) { spinner.classList.add('d-none'); }
        }

        function clearOutput() {
            if (partialEl) { partialEl.classList.add('d-none'); }
            if (resultEl)  { resultEl.classList.add('d-none'); resultEl.innerHTML = ''; }
            if (errorsWrap) { errorsWrap.classList.add('d-none'); }
            if (errorsList) { errorsList.innerHTML = ''; }
        }

        function render(r) {
            const status = r.status || 'failed';

            // Estados de transporte (sin resultado del backend).
            if (status === 'unavailable') { setStatus(r.errorkey || 'backendunavailable'); return; }
            if (status === 'failed' && r.errorkey) { setStatus(r.errorkey); return; }

            // Estado principal: ok | partial | failed.
            var statuskey = status === 'ok' ? 'statusok'
                : (status === 'partial' ? 'statuspartial' : 'statusfailed');
            setStatus(statuskey);

            if (status === 'partial' && partialEl) { partialEl.classList.remove('d-none'); }

            // Detalle de contadores.
            if (resultEl) {
                var rows = [
                    ['resdocsmodified', r.documents_modified],
                    ['resdocsnew', r.documents_new],
                    ['resdocsdeleted', r.documents_deleted],
                    ['reschunksadded', r.chunks_added],
                    ['reschunksstale', r.chunks_deleted_or_stale],
                    ['resembeddings', r.embedding_recomputed],
                    ['resvisibility', r.visibility_updates]
                ];
                resultEl.innerHTML = '';
                rows.forEach(function(row) {
                    var li = document.createElement('li');
                    Str.get_string(row[0], 'block_ragindexer').then(function(label) {
                        li.textContent = label + ': ' + (row[1] || 0);
                        return label;
                    }).catch(function() { return null; });
                    resultEl.appendChild(li);
                });
                resultEl.classList.remove('d-none');
            }

            // Resumen de errores (texto escapado).
            if (r.errors && r.errors.length && errorsWrap && errorsList) {
                errorsList.innerHTML = '';
                r.errors.forEach(function(e) {
                    var li = document.createElement('li');
                    li.textContent = e;
                    errorsList.appendChild(li);
                });
                errorsWrap.classList.remove('d-none');
            }
        }

        function setStatus(strkey) {
            if (!statusEl) { return; }
            Str.get_string(strkey, 'block_ragindexer').then(function(msg) {
                statusEl.textContent = msg;
                return msg;
            }).catch(function() {
                statusEl.textContent = strkey;
                return null;
            });
        }
    };

    return {init: init};
});
