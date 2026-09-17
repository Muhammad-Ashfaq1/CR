/**
 * AwtDocPreview — render a private document in place instead of downloading it.
 *
 * ── Using it ──────────────────────────────────────────────────────────────
 *
 * Declaratively, which is how both compliance pages use it:
 *
 *   <button type="button"
 *           data-awt-preview
 *           data-awt-preview-url="{{ route(…preview…) }}"
 *           data-awt-preview-download="{{ route(…download…) }}"
 *           data-awt-preview-name="bank-letter.pdf"
 *           data-awt-preview-kind="pdf"          {{-- pdf | image | none --}}
 *           data-awt-preview-meta="Business Verification · Uploaded Aug 4">
 *
 * …with @include('partials.document-preview-modal') somewhere on the page.
 *
 * Or from script, to draw into a pane that is not the modal — the reviewer's
 * decision dialog embeds one so the document is on screen while the decision is
 * being made:
 *
 *   AwtDocPreview.render(stageElement, { url, kind, name });
 *
 * ── Two things it is careful about ────────────────────────────────────────
 *
 *  • `kind` is decided by the SERVER from the stored filename, not sniffed
 *    here. A viewer that guesses would eventually guess wrong and hand a file
 *    to the wrong renderer.
 *  • The frame is emptied when the modal closes. Leaving it would keep a
 *    private compliance document loaded behind whatever the user does next,
 *    and keep the browser holding a decrypted copy of it in memory.
 */
(function () {
    'use strict';

    if (window.AwtDocPreview) return;

    function clear(stage) {
        if (stage) stage.innerHTML = '';
    }

    /**
     * Draw one document into a stage element.
     *
     * The <iframe> is sandboxed: a PDF viewer needs no script, no forms and no
     * access to this origin, and the response's own CSP says the same thing
     * from the other side.
     */
    function render(stage, options) {
        if (!stage) return;
        clear(stage);

        var kind = (options && options.kind) || 'none';
        var url = (options && options.url) || '';
        var name = (options && options.name) || 'document';

        if (!url || kind === 'none') {
            var fallback = document.createElement('div');
            fallback.className = 'awt-preview-empty';
            fallback.innerHTML =
                '<i class="icon-base ti tabler-file-description"></i>' +
                '<p class="awt-preview-empty-title">No in-page preview for this file type</p>' +
                '<p class="awt-preview-empty-text">Word documents cannot be rendered in a browser. ' +
                'Download it to read it — the file itself is unchanged.</p>';
            stage.appendChild(fallback);
            return;
        }

        if (kind === 'image') {
            var img = document.createElement('img');
            img.className = 'awt-preview-image';
            img.alt = name;
            img.src = url;
            stage.appendChild(img);
            return;
        }

        var frame = document.createElement('iframe');
        frame.className = 'awt-preview-frame';
        frame.title = name;
        frame.setAttribute('sandbox', '');
        frame.src = url;
        stage.appendChild(frame);
    }

    window.AwtDocPreview = { render: render, clear: clear };

    /* ── The shared modal ────────────────────────────────────────────── */

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest ? event.target.closest('[data-awt-preview]') : null;
        if (!trigger) return;

        var modal = document.getElementById('awtDocPreview');
        if (!modal || !window.bootstrap || !window.bootstrap.Modal) return;   // link/button still works

        event.preventDefault();

        var name = trigger.getAttribute('data-awt-preview-name') || 'Document';
        var downloadUrl = trigger.getAttribute('data-awt-preview-download') || '';
        var url = trigger.getAttribute('data-awt-preview-url') || '';

        var title = modal.querySelector('#awtDocPreviewTitle');
        if (title) {
            title.textContent = name;
            title.setAttribute('title', name);
        }

        var sub = modal.querySelector('[data-awt-preview-sub]');
        if (sub) sub.textContent = trigger.getAttribute('data-awt-preview-meta') || '';

        var download = modal.querySelector('[data-awt-preview-download]');
        if (download) download.setAttribute('href', downloadUrl);

        // "New tab" opens the inline URL, not the download one: the point of it
        // is a bigger viewport, not a second way to save the file.
        var open = modal.querySelector('[data-awt-preview-open]');
        if (open) {
            open.setAttribute('href', url || downloadUrl);
            open.hidden = !url;
        }

        render(modal.querySelector('[data-awt-preview-stage]'), {
            url: url,
            kind: trigger.getAttribute('data-awt-preview-kind') || 'none',
            name: name,
        });

        window.bootstrap.Modal.getOrCreateInstance(modal).show();
    });

    // Drop the document the moment the viewer closes.
    document.addEventListener('hidden.bs.modal', function (event) {
        if (!event.target || event.target.id !== 'awtDocPreview') return;
        clear(event.target.querySelector('[data-awt-preview-stage]'));
    });
})();
