define(['core/ajax', 'core/notification', 'core/str'], function(Ajax, Notification, Str) {

    const init = function(config) {
        const root = document.querySelector('[data-region="ragindexer"]');
        if (!root) { return; }

        const indexBtn    = root.querySelector('[data-action="index-course"]');
        const spinner     = root.querySelector('.ragindexer-spinner');
        const indexStatus = root.querySelector('[data-region="index-status"]');

        if (!indexBtn) { return; }

        indexBtn.addEventListener('click', function() {
            indexBtn.disabled = true;
            if (spinner) { spinner.classList.remove('d-none'); }
            setText(indexStatus, 'indexing');

            Ajax.call([{
                methodname: 'block_ragindexer_index_course',
                args: {courseid: config.courseid, forcereindex: false}
            }])[0].then(function(response) {
                setText(indexStatus, response.status === 'error' ? 'indexerror' : 'indexok');
                done();
                return response;
            }).catch(function(error) {
                setText(indexStatus, 'indexerror');
                done();
                Notification.exception(error);
            });
        });

        function done() {
            indexBtn.disabled = false;
            if (spinner) { spinner.classList.add('d-none'); }
        }

        function setText(el, strkey) {
            if (!el) { return; }
            Str.get_string(strkey, 'block_ragindexer').then(function(msg) {
                el.textContent = msg;
                return msg;
            }).catch(function() {
                return null;
            });
        }
    };

    return {init: init};
});
