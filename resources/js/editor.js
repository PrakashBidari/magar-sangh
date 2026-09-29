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
            'link', 'image', 'file', 'video', 'table', 'hr', 'symbols', '|',
            'source', 'fullsize',
        ],
        buttonsMD: [
            'undo', 'redo', '|', 'paragraph', 'fontsize', 'brush', '|', 'bold', 'italic', 'underline', '|',
            'ul', 'ol', 'align', '|', 'link', 'image', 'file', 'table', '|', 'source', 'fullsize',
        ],
        buttonsSM: [
            'bold', 'italic', 'underline', '|', 'fontsize', 'brush', '|', 'ul', 'ol', 'align', '|', 'link', 'image', 'file', '|', 'source',
        ],
        buttonsXS: ['bold', 'italic', '|', 'fontsize', '|', 'ul', 'ol', '|', 'link', 'image', 'file', '|', 'source'],
        // Google fonts in this list are loaded on the site and dashboard by the <link> in both layouts.
        controls: {
            font: {
                list: {
                    '': 'Default',
                    // Sans serif
                    'Arial, Helvetica, sans-serif': 'Arial',
                    'Verdana, Geneva, sans-serif': 'Verdana',
                    'Tahoma, Geneva, sans-serif': 'Tahoma',
                    "'Trebuchet MS', Helvetica, sans-serif": 'Trebuchet MS',
                    "'Noto Sans', sans-serif": 'Noto Sans',
                    'Poppins, sans-serif': 'Poppins',
                    'Roboto, sans-serif': 'Roboto',
                    "'Open Sans', sans-serif": 'Open Sans',
                    'Lato, sans-serif': 'Lato',
                    'Montserrat, sans-serif': 'Montserrat',
                    'Oswald, sans-serif': 'Oswald',
                    // Serif
                    "Georgia, serif": 'Georgia',
                    "'Times New Roman', Times, serif": 'Times New Roman',
                    "'Playfair Display', serif": 'Playfair Display',
                    'Merriweather, serif': 'Merriweather',
                    // Cursive / handwriting
                    "'Dancing Script', cursive": 'Dancing Script',
                    "'Great Vibes', cursive": 'Great Vibes',
                    'Pacifico, cursive': 'Pacifico',
                    'Satisfy, cursive': 'Satisfy',
                    'Caveat, cursive': 'Caveat',
                    'Lobster, cursive': 'Lobster',
                    "'Comic Sans MS', 'Comic Sans', cursive": 'Comic Sans MS',
                    // Monospace
                    "'Courier New', Courier, monospace": 'Courier New',
                    // Nepali (Devanagari)
                    "'Noto Sans Devanagari', sans-serif": 'Noto Sans Devanagari (नेपाली)',
                    'Mukta, sans-serif': 'Mukta (नेपाली)',
                    "'Yatra One', cursive": 'Yatra One (नेपाली)',
                },
            },
        },
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
            // Dropped / pasted files: images are placed inline, PDFs become a download link.
            defaultHandlerSuccess(data) {
                (data.files || []).forEach((file) => {
                    if (/\.pdf$/i.test(file)) {
                        this.selection.insertHTML(`<p><a href="${file}" target="_blank" rel="noopener">📄 ${file.split('/').pop()}</a></p>`);
                    } else {
                        this.selection.insertImage(file, null, 400);
                    }
                });
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
