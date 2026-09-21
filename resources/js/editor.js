// The prebuilt bundle ships every toolbar icon (the ESM entry leaves some out).
import * as JoditModule from 'jodit/es2021/jodit.min.js';

const Jodit = JoditModule.Jodit ?? JoditModule.default?.Jodit ?? JoditModule.default;

const token = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

/**
 * Turn a <textarea> into a rich text editor (font size, colours, alignment, tables,
 * images uploaded from the computer, video embeds and a source view).
 * `onChange(html)` fires on every edit. Returns the Jodit instance.
 */
function createEditor(element, uploadUrl, onChange) {
    const editor = Jodit.make(element, {
        height: 420,
        minHeight: 260,
        toolbarAdaptive: true,
        toolbarSticky: false,
        showCharsCounter: false,
        showWordsCounter: false,
        showXPathInStatusbar: false,
        askBeforePasteHTML: false,
        defaultActionOnPaste: 'insert_clear_html',
        buttons: [
            'undo', 'redo', '|',
            'paragraph', 'fontsize', 'font', 'brush', '|',
            'bold', 'italic', 'underline', 'strikethrough', 'eraser', '|',
            'ul', 'ol', 'indent', 'outdent', '|',
            'align', '|',
            'link', 'image', 'video', 'table', 'hr', 'symbols', '|',
            'source', 'fullsize',
        ],
        buttonsMD: [
            'undo', 'redo', '|', 'paragraph', 'fontsize', 'brush', '|', 'bold', 'italic', 'underline', '|',
            'ul', 'ol', 'align', '|', 'link', 'image', 'table', '|', 'source', 'fullsize',
        ],
        buttonsSM: [
            'bold', 'italic', 'underline', '|', 'fontsize', 'brush', '|', 'ul', 'ol', 'align', '|', 'link', 'image', '|', 'source',
        ],
        buttonsXS: ['bold', 'italic', '|', 'fontsize', '|', 'ul', 'ol', '|', 'link', 'image', '|', 'source'],
        uploader: {
            url: uploadUrl,
            format: 'json',
            method: 'POST',
            withCredentials: true,
            filesVariableName: () => 'upload',
            headers: { 'X-CSRF-TOKEN': token(), Accept: 'application/json' },
            isSuccess: (resp) => Boolean(resp && resp.url),
            getMessage: (resp) => (resp && resp.message) || '',
            process: (resp) => ({ files: [resp.url], path: '', baseurl: '', error: resp.url ? 0 : 1, message: resp.message || '' }),
            defaultHandlerSuccess(data) {
                (data.files || []).forEach((file) => this.selection.insertImage(file, null, 400));
            },
            error(e) {
                this.message.error(e.message || 'Upload failed');
            },
        },
    });

    if (onChange) {
        editor.events.on('change', (value) => onChange(value));
    }

    return editor;
}

// Vite drops exports from entry files, so the loader in the dashboard layout calls this global.
window.createRichEditor = createEditor;
