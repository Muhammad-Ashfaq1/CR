{{--
    The document viewer.

    One modal per page, re-pointed at whichever file was asked for — see
    awt-doc-preview.js. Any trigger carrying `data-awt-preview` opens it, so the
    tenant's own compliance page and the reviewer's case page share a viewer
    instead of each growing one.

    It renders the file from the `preview` route, which serves it inline with a
    locked-down Content-Type and a CSP that makes the document inert. Download
    stays one click away, because a preview is not a substitute for keeping a
    copy — and for a .docx, which no browser renders, it is the only option and
    the viewer says so rather than showing an empty frame.
--}}
<div class="modal fade awt-preview" id="awtDocPreview" tabindex="-1" aria-hidden="true" aria-labelledby="awtDocPreviewTitle">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header awt-preview-header">
                <div class="min-w-0">
                    <h5 class="modal-title text-truncate" id="awtDocPreviewTitle">Document</h5>
                    <span class="awt-preview-sub" data-awt-preview-sub></span>
                </div>

                <div class="awt-preview-tools">
                    <a href="#" class="btn btn-sm btn-outline-secondary" data-awt-preview-open target="_blank" rel="noopener">
                        <i class="icon-base ti tabler-external-link me-1"></i>New tab
                    </a>
                    <a href="#" class="btn btn-sm btn-primary" data-awt-preview-download>
                        <i class="icon-base ti tabler-download me-1"></i>Download
                    </a>
                    <button type="button" class="btn-close ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body awt-preview-body">
                <div class="awt-preview-stage" data-awt-preview-stage></div>
            </div>
        </div>
    </div>
</div>
