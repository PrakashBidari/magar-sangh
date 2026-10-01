<?php

use App\Http\Controllers\Admin\AccountingCategoryController;
use App\Http\Controllers\Admin\CommitteeMemberController;
use App\Http\Controllers\Admin\CommitteeSubTypeController;
use App\Http\Controllers\Admin\CommitteeTypeController;
use App\Http\Controllers\Admin\DonationController;
use App\Http\Controllers\Admin\MembershipTypeController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\UserController;
use App\Models\AccountingCategory;
use App\Models\Article;
use App\Models\CommitteeMember;
use App\Models\CommitteeSubType;
use App\Models\CommitteeType;
use App\Models\ContactMessage;
use App\Models\Donation;
use App\Models\Event;
use App\Models\GalleryPhoto;
use App\Models\GalleryVideo;
use App\Models\HeroSlide;
use App\Models\MembershipType;
use App\Models\News;
use App\Models\NotificationItem;
use App\Models\Publication;
use App\Models\Role;
use App\Models\SisterOrganization;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Dashboard resources
|--------------------------------------------------------------------------
|
| Every entry here becomes a full dashboard section (DataTable list plus
| create / edit / delete) served by App\Http\Controllers\Admin\ResourceController.
|
| Field types: text, url, number, date, datetime, time, textarea, richtext,
| image, file, checkbox, select, password.
| Column types: text, image, date, datetime, boolean, file, money, roles.
|
*/

return [
    'groups' => [
        'home' => 'Homepage',
        'media' => 'Media & Information',
        'gallery' => 'Gallery',
        'organization' => 'Organization',
        'membership' => 'Membership',
        'sifaris' => 'Sifaris',
        'donation' => 'Lakhan Thapa Pratisthan',
        'accounting' => 'Accounting',
        'inbox' => 'Inbox',
        'access' => 'Roles & Permissions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    |
    | Each resource below gets "{key}.view / create / edit / delete" permissions
    | automatically (read-only ones: view / delete). The dashboard pages that are
    | not resources are listed here. See App\Support\Permissions.
    |
    | The "admin" role always has every permission and cannot be changed.
    |
    */
    'permission_sections' => [
        'settings' => ['group' => 'general', 'label' => 'Site & About Settings', 'icon' => '⚙️', 'actions' => ['manage']],
        'membership-applications' => ['group' => 'membership', 'label' => 'Membership Applications', 'icon' => '📝', 'actions' => ['view', 'edit', 'approve', 'delete']],
        'membership-settings' => ['group' => 'membership', 'label' => 'Membership Settings', 'icon' => '⚙️', 'actions' => ['manage']],
        'sifaris' => ['group' => 'sifaris', 'label' => 'Sifaris Requests', 'icon' => '📜', 'actions' => ['view', 'edit', 'approve', 'delete']],
        'sifaris-settings' => ['group' => 'sifaris', 'label' => 'Sifaris Settings', 'icon' => '⚙️', 'actions' => ['manage']],
        'donation-settings' => ['group' => 'donation', 'label' => 'Donation Page Settings', 'icon' => '⚙️', 'actions' => ['manage']],
        'accounting' => ['group' => 'accounting', 'label' => 'Income & Expense Book', 'icon' => '📒', 'actions' => ['view', 'create', 'edit', 'delete']],
        'roles' => ['group' => 'access', 'label' => 'Roles & Permissions', 'icon' => '🛡️', 'actions' => ['view', 'create', 'edit', 'delete']],
    ],

    'resources' => [

        // ---------------------------------------------------------------- Homepage
        'hero-slides' => [
            'group' => 'home',
            'label' => 'Hero Slider',
            'singular' => 'Slide',
            'icon' => '🏞️',
            'model' => HeroSlide::class,
            'order' => ['sort_order', 'asc'],
            'columns' => [
                ['field' => 'image_url', 'label' => 'Image', 'type' => 'image'],
                ['field' => 'title_np', 'label' => 'Heading (Nepali)', 'class' => 'np'],
                ['field' => 'title_en', 'label' => 'Heading (English)'],
                ['field' => 'sort_order', 'label' => 'Order'],
                ['field' => 'is_active', 'label' => 'Visible', 'type' => 'boolean'],
            ],
            'fields' => [
                ['name' => 'image_url', 'label' => 'Slide Image', 'type' => 'image', 'folder' => 'hero', 'required_on_create' => true, 'max' => 6144, 'help' => 'Wide image, ideally 1600 x 900 or larger.'],
                ['name' => 'title_np', 'label' => 'Heading (Nepali)', 'type' => 'text', 'rules' => 'nullable|string|max:255', 'class' => 'np'],
                ['name' => 'title_en', 'label' => 'Heading (English)', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
                ['name' => 'description', 'label' => 'Short Text', 'type' => 'textarea', 'rules' => 'nullable|string|max:500', 'help' => 'Leave blank to use the short About text from Site Settings.'],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'rules' => 'nullable|integer|min:0', 'width' => 'half', 'default' => 0],
                ['name' => 'is_active', 'label' => 'Show this slide on the homepage', 'type' => 'checkbox', 'width' => 'half', 'default' => true],
            ],
        ],

        // ---------------------------------------------------------------- Media & Info
        'news' => [
            'group' => 'media',
            'label' => 'News',
            'singular' => 'News',
            'icon' => '📰',
            'model' => News::class,
            'controller' => NewsController::class,
            'approvable' => true, // adds the "news.approve" permission and Approve / Disapprove buttons
            'order' => ['published_at', 'desc'],
            'columns' => [
                ['field' => 'image_url', 'label' => 'Image', 'type' => 'image'],
                ['field' => 'title', 'label' => 'Title'],
                ['field' => 'published_at', 'label' => 'Published', 'type' => 'datetime'],
                ['field' => 'status', 'label' => 'Status', 'type' => 'status'],
            ],
            'fields' => [
                ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => 'required|string|max:255'],
                ['name' => 'slug', 'label' => 'Slug (URL)', 'type' => 'text', 'rules' => 'nullable|string|max:255', 'help' => 'Leave blank to generate from the title.', 'width' => 'half'],
                ['name' => 'published_at', 'label' => 'Publish Date & Time', 'type' => 'datetime', 'rules' => 'nullable|date', 'width' => 'half', 'default' => 'now'],
                ['name' => 'excerpt', 'label' => 'Short Summary', 'type' => 'textarea', 'rules' => 'nullable|string|max:255'],
                ['name' => 'image_url', 'label' => 'Cover Image', 'type' => 'image', 'folder' => 'news'],
                ['name' => 'body', 'label' => 'Description', 'type' => 'richtext', 'rules' => 'required|string'],
            ],
        ],

        'articles' => [
            'group' => 'media',
            'label' => 'Articles',
            'singular' => 'Article',
            'icon' => '✍️',
            'model' => Article::class,
            'order' => ['published_at', 'desc'],
            'columns' => [
                ['field' => 'image_url', 'label' => 'Image', 'type' => 'image'],
                ['field' => 'title', 'label' => 'Title'],
                ['field' => 'author', 'label' => 'Author'],
                ['field' => 'published_at', 'label' => 'Published', 'type' => 'datetime'],
            ],
            'fields' => [
                ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => 'required|string|max:255'],
                ['name' => 'slug', 'label' => 'Slug (URL)', 'type' => 'text', 'rules' => 'nullable|string|max:255', 'help' => 'Leave blank to generate from the title.', 'width' => 'half'],
                ['name' => 'author', 'label' => 'Author', 'type' => 'text', 'rules' => 'nullable|string|max:255', 'width' => 'half'],
                ['name' => 'published_at', 'label' => 'Publish Date & Time', 'type' => 'datetime', 'rules' => 'nullable|date', 'width' => 'half', 'default' => 'now'],
                ['name' => 'excerpt', 'label' => 'Short Summary', 'type' => 'textarea', 'rules' => 'nullable|string|max:255'],
                ['name' => 'image_url', 'label' => 'Cover Image', 'type' => 'image', 'folder' => 'articles'],
                ['name' => 'body', 'label' => 'Description', 'type' => 'richtext', 'rules' => 'required|string'],
            ],
        ],

        'notifications' => [
            'group' => 'media',
            'label' => 'Notifications',
            'singular' => 'Notification',
            'icon' => '🔔',
            'model' => NotificationItem::class,
            'order' => ['published_at', 'desc'],
            'columns' => [
                ['field' => 'image_url', 'label' => 'Image', 'type' => 'image'],
                ['field' => 'title', 'label' => 'Title'],
                ['field' => 'published_at', 'label' => 'Published', 'type' => 'datetime'],
                ['field' => 'show_popup', 'label' => 'Popup', 'type' => 'boolean'],
            ],
            'fields' => [
                ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => 'required|string|max:255'],
                ['name' => 'slug', 'label' => 'Slug (URL)', 'type' => 'text', 'rules' => 'nullable|string|max:255', 'help' => 'Leave blank to generate from the title.', 'width' => 'half'],
                ['name' => 'published_at', 'label' => 'Publish Date & Time', 'type' => 'datetime', 'rules' => 'nullable|date', 'width' => 'half', 'default' => 'now'],
                ['name' => 'image_url', 'label' => 'Image', 'type' => 'image', 'folder' => 'notifications', 'max' => 6144, 'help' => 'Optional. Shown on the notification page and in the popup.'],
                ['name' => 'body', 'label' => 'Description', 'type' => 'richtext', 'rules' => 'nullable|string'],
                // Popup on the public website (see resources/views/partials/notification-popup.blade.php).
                ['name' => 'show_popup', 'label' => 'Show this notification as a popup on the website', 'type' => 'checkbox', 'default' => false],
                ['name' => 'popup_pages', 'label' => 'Popup: where', 'type' => 'select', 'options' => ['home' => 'Home page only', 'all' => 'Every page'], 'rules' => 'nullable|in:home,all', 'width' => 'half', 'default' => 'home', 'show_if' => 'show_popup'],
                ['name' => 'popup_frequency', 'label' => 'Popup: how often', 'type' => 'select', 'options' => ['once' => 'Only the first time a visitor comes', 'always' => 'Every time a page is loaded'], 'rules' => 'nullable|in:once,always', 'width' => 'half', 'default' => 'once', 'show_if' => 'show_popup'],
                ['name' => 'popup_repeat_minutes', 'label' => 'Popup: show again after every … minutes', 'type' => 'number', 'rules' => 'nullable|integer|min:1|max:1440', 'width' => 'half', 'help' => 'Leave empty to not repeat. e.g. 10 = again every 10 minutes while the visitor stays on the website.', 'show_if' => 'show_popup'],
                ['name' => 'popup_on_exit', 'label' => 'Also show when the visitor is about to leave the website', 'type' => 'checkbox', 'width' => 'half', 'default' => false, 'show_if' => 'show_popup', 'help' => 'Works on computers (mouse moves up to close the tab), once per visit.'],
            ],
        ],

        'publications' => [
            'group' => 'media',
            'label' => 'Publications',
            'singular' => 'Publication',
            'icon' => '📚',
            'model' => Publication::class,
            'order' => ['published_date', 'desc'],
            'columns' => [
                ['field' => 'title', 'label' => 'Title'],
                ['field' => 'file_url', 'label' => 'File', 'type' => 'file'],
                ['field' => 'published_date', 'label' => 'Published', 'type' => 'date'],
            ],
            'fields' => [
                ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => 'required|string|max:255'],
                ['name' => 'slug', 'label' => 'Slug (URL)', 'type' => 'text', 'rules' => 'nullable|string|max:255', 'help' => 'Leave blank to generate from the title.', 'width' => 'half'],
                ['name' => 'published_date', 'label' => 'Published Date', 'type' => 'date', 'rules' => 'nullable|date', 'width' => 'half', 'default' => 'today'],
                ['name' => 'file_url', 'label' => 'File (PDF / Document)', 'type' => 'file', 'folder' => 'publications', 'required_on_create' => true, 'mimes' => 'pdf,doc,docx,xls,xlsx,ppt,pptx,zip', 'max' => 20480],
            ],
        ],

        'events' => [
            'group' => 'media',
            'label' => 'Events',
            'singular' => 'Event',
            'icon' => '📅',
            'model' => Event::class,
            'order' => ['event_date', 'desc'],
            'columns' => [
                ['field' => 'image_url', 'label' => 'Image', 'type' => 'image'],
                ['field' => 'title', 'label' => 'Title'],
                ['field' => 'location', 'label' => 'Location'],
                ['field' => 'event_date', 'label' => 'Date', 'type' => 'date'],
            ],
            'fields' => [
                ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => 'required|string|max:255'],
                ['name' => 'slug', 'label' => 'Slug (URL)', 'type' => 'text', 'rules' => 'nullable|string|max:255', 'help' => 'Leave blank to generate from the title.', 'width' => 'half'],
                ['name' => 'location', 'label' => 'Location', 'type' => 'text', 'rules' => 'nullable|string|max:255', 'width' => 'half'],
                ['name' => 'event_date', 'label' => 'Event Date', 'type' => 'date', 'rules' => 'required|date', 'width' => 'half', 'default' => 'today'],
                ['name' => 'event_time', 'label' => 'Event Time', 'type' => 'time', 'rules' => 'nullable|date_format:H:i', 'width' => 'half'],
                ['name' => 'image_url', 'label' => 'Cover Image', 'type' => 'image', 'folder' => 'events'],
                ['name' => 'description', 'label' => 'Description', 'type' => 'richtext', 'rules' => 'nullable|string'],
            ],
        ],

        // ---------------------------------------------------------------- Gallery
        'gallery-photos' => [
            'group' => 'gallery',
            'label' => 'Photo Gallery',
            'singular' => 'Photo',
            'icon' => '🖼️',
            'model' => GalleryPhoto::class,
            'order' => ['id', 'desc'],
            'columns' => [
                ['field' => 'image_url', 'label' => 'Photo', 'type' => 'image'],
                ['field' => 'title', 'label' => 'Title'],
                ['field' => 'category', 'label' => 'Category'],
                ['field' => 'created_at', 'label' => 'Added', 'type' => 'date'],
            ],
            'fields' => [
                ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => 'nullable|string|max:255', 'width' => 'half'],
                ['name' => 'category', 'label' => 'Category', 'type' => 'text', 'rules' => 'nullable|string|max:255', 'width' => 'half', 'suggest' => true, 'help' => 'Pick an existing category or type a new one.'],
                ['name' => 'image_url', 'label' => 'Photo', 'type' => 'image', 'folder' => 'gallery', 'required_on_create' => true],
            ],
        ],

        'gallery-videos' => [
            'group' => 'gallery',
            'label' => 'Video Gallery',
            'singular' => 'Video',
            'icon' => '🎬',
            'model' => GalleryVideo::class,
            'order' => ['id', 'desc'],
            'columns' => [
                ['field' => 'thumbnail_url', 'label' => 'Thumbnail', 'type' => 'image'],
                ['field' => 'title', 'label' => 'Title'],
                ['field' => 'youtube_embed_url', 'label' => 'YouTube Link'],
            ],
            'fields' => [
                ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
                ['name' => 'youtube_embed_url', 'label' => 'YouTube Link', 'type' => 'url', 'rules' => 'required|url|max:255', 'transform' => 'youtube_embed', 'help' => 'Paste any YouTube link (watch, share or embed).'],
                ['name' => 'thumbnail_url', 'label' => 'Thumbnail', 'type' => 'image', 'folder' => 'gallery', 'help' => 'Optional. The YouTube thumbnail is used when left empty.'],
            ],
        ],

        // ---------------------------------------------------------------- Organization
        'committee-types' => [
            'group' => 'organization',
            'label' => 'Committee Types',
            'singular' => 'Committee Type',
            'icon' => '🗂️',
            'model' => CommitteeType::class,
            'controller' => CommitteeTypeController::class,
            'order' => ['sort_order', 'asc'],
            'columns' => [
                ['field' => 'name_np', 'label' => 'Name (Nepali)', 'class' => 'np'],
                ['field' => 'name_en', 'label' => 'Name (English)'],
                ['field' => 'members_count', 'label' => 'Members'],
                ['field' => 'sort_order', 'label' => 'Order'],
            ],
            'fields' => [
                ['name' => 'name_np', 'label' => 'Name (Nepali)', 'type' => 'text', 'rules' => 'required|string|max:255', 'width' => 'half', 'class' => 'np'],
                ['name' => 'name_en', 'label' => 'Name (English)', 'type' => 'text', 'rules' => 'nullable|string|max:255', 'width' => 'half'],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'rules' => 'nullable|integer|min:0', 'width' => 'half', 'default' => 0, 'help' => 'Committees are listed on the committee page in this order.'],
            ],
        ],

        'committee-sub-types' => [
            'group' => 'organization',
            'label' => 'Committee Sub Types',
            'singular' => 'Sub Type',
            'icon' => '🗃️',
            'model' => CommitteeSubType::class,
            'controller' => CommitteeSubTypeController::class,
            'order' => ['committee_type_id', 'asc'],
            'columns' => [
                ['field' => 'name_np', 'label' => 'Sub Type (Nepali)', 'class' => 'np'],
                ['field' => 'name_en', 'label' => 'Sub Type (English)'],
                ['field' => 'committeeType.name_np', 'label' => 'Committee', 'class' => 'np'],
                ['field' => 'members_count', 'label' => 'Members'],
                ['field' => 'sort_order', 'label' => 'Order'],
            ],
            'fields' => [
                ['name' => 'committee_type_id', 'label' => 'Committee', 'type' => 'select', 'options_from' => CommitteeType::class, 'placeholder' => '— Select committee —', 'rules' => 'required|integer|exists:committee_types,id', 'help' => 'e.g. District Committee.'],
                ['name' => 'name_np', 'label' => 'Sub Type Name (Nepali)', 'type' => 'text', 'rules' => 'required|string|max:255', 'width' => 'half', 'class' => 'np', 'help' => 'e.g. काठमाडौं'],
                ['name' => 'name_en', 'label' => 'Sub Type Name (English)', 'type' => 'text', 'rules' => 'nullable|string|max:255', 'width' => 'half', 'help' => 'e.g. Kathmandu'],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'rules' => 'nullable|integer|min:0', 'width' => 'half', 'default' => 0, 'help' => 'Tabs on the committee page follow this order.'],
            ],
        ],

        'committee' => [
            'group' => 'organization',
            'label' => 'Committee & Presidents',
            'singular' => 'Member',
            'icon' => '🏛️',
            'model' => CommitteeMember::class,
            'controller' => CommitteeMemberController::class,
            'order' => ['sort_order', 'asc'],
            'columns' => [
                ['field' => 'photo_url', 'label' => 'Photo', 'type' => 'image'],
                ['field' => 'name', 'label' => 'Name'],
                ['field' => 'phone', 'label' => 'Phone'],
                ['field' => 'position_np', 'label' => 'Position'],
                ['field' => 'committeeType.name_np', 'label' => 'Committee', 'class' => 'np'],
                ['field' => 'committeeSubType.name_np', 'label' => 'Sub Type', 'class' => 'np'],
                ['field' => 'term_label', 'label' => 'Term'],
                ['field' => 'is_current', 'label' => 'Current', 'type' => 'boolean'],
                ['field' => 'is_past_president', 'label' => 'Past President', 'type' => 'boolean'],
                ['field' => 'show_on_homepage', 'label' => 'Homepage', 'type' => 'boolean'],
                ['field' => 'sort_order', 'label' => 'Order'],
            ],
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:255', 'width' => 'half'],
                ['name' => 'phone', 'label' => 'Phone Number', 'type' => 'tel', 'rules' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9][0-9\s\-]{6,18}$/'], 'width' => 'half', 'help' => 'e.g. 98XXXXXXXX. Only shown in the dashboard.'],
                ['name' => 'committee_type_id', 'label' => 'Committee', 'type' => 'select', 'options_from' => CommitteeType::class, 'placeholder' => '— Select committee —', 'rules' => 'required|integer|exists:committee_types,id', 'help' => 'Add committees under Committee Types.', 'width' => 'half'],
                ['name' => 'committee_sub_type_id', 'label' => 'Sub Type', 'type' => 'select', 'options_from' => CommitteeSubType::class, 'depends_on' => 'committee_type_id', 'placeholder' => '— None (whole committee) —', 'rules' => 'nullable|integer', 'help' => 'Optional, e.g. Kathmandu for the District Committee. Add them under Committee Sub Types.', 'width' => 'half'],
                ['name' => 'photo_url', 'label' => 'Photo', 'type' => 'image', 'folder' => 'committee'],
                ['name' => 'position_np', 'label' => 'Position (Nepali)', 'type' => 'text', 'rules' => 'required|string|max:255', 'width' => 'half', 'class' => 'np'],
                ['name' => 'position_en', 'label' => 'Position (English)', 'type' => 'text', 'rules' => 'nullable|string|max:255', 'width' => 'half'],
                ['name' => 'term_label', 'label' => 'Term Label', 'type' => 'text', 'rules' => 'nullable|string|max:255', 'help' => 'e.g. 2078 – 2082', 'width' => 'half', 'class' => 'np'],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'rules' => 'nullable|integer|min:0', 'width' => 'half', 'default' => 0],
                ['name' => 'term_start', 'label' => 'Term Start', 'type' => 'date', 'rules' => 'nullable|date', 'width' => 'half'],
                ['name' => 'term_end', 'label' => 'Term End', 'type' => 'date', 'rules' => 'nullable|date|after_or_equal:term_start', 'width' => 'half'],
                ['name' => 'is_current', 'label' => 'Part of the current committee', 'type' => 'checkbox'],
                ['name' => 'is_past_president', 'label' => 'Is a past president', 'type' => 'checkbox'],
                ['name' => 'show_on_homepage', 'label' => 'Show on the homepage', 'type' => 'checkbox', 'default' => false],
            ],
        ],

        'sister-organizations' => [
            'group' => 'organization',
            'label' => 'Sister Organizations',
            'singular' => 'Sister Organization',
            'icon' => '🤝',
            'model' => SisterOrganization::class,
            'order' => ['sort_order', 'asc'],
            'columns' => [
                ['field' => 'logo_url', 'label' => 'Logo', 'type' => 'image', 'fit' => 'contain'],
                ['field' => 'name_np', 'label' => 'Name (Nepali)'],
                ['field' => 'name_en', 'label' => 'Name (English)'],
                ['field' => 'link', 'label' => 'Website'],
                ['field' => 'sort_order', 'label' => 'Order'],
            ],
            'fields' => [
                ['name' => 'name_np', 'label' => 'Name (Nepali)', 'type' => 'text', 'rules' => 'required|string|max:255', 'width' => 'half', 'class' => 'np'],
                ['name' => 'name_en', 'label' => 'Name (English)', 'type' => 'text', 'rules' => 'nullable|string|max:255', 'width' => 'half'],
                ['name' => 'link', 'label' => 'Website Link', 'type' => 'url', 'rules' => 'nullable|url|max:255', 'width' => 'half'],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'rules' => 'nullable|integer|min:0', 'width' => 'half', 'default' => 0],
                ['name' => 'logo_url', 'label' => 'Logo', 'type' => 'image', 'folder' => 'sister-organizations'],
                ['name' => 'blurb', 'label' => 'Short Description', 'type' => 'textarea', 'rules' => 'nullable|string|max:255'],
            ],
        ],

        // ---------------------------------------------------------------- Membership
        // The application lists (pending / approved / disapproved) are custom pages, see routes/web.php.
        'membership-types' => [
            'group' => 'membership',
            'label' => 'Membership Types',
            'singular' => 'Membership Type',
            'icon' => '🏷️',
            'model' => MembershipType::class,
            'controller' => MembershipTypeController::class,
            'order' => ['sort_order', 'asc'],
            'columns' => [
                ['field' => 'name_en', 'label' => 'Name (English)'],
                ['field' => 'name_np', 'label' => 'Name (Nepali)', 'class' => 'np'],
                ['field' => 'duration_label', 'label' => 'Duration'],
                ['field' => 'fee_label', 'label' => 'Price'],
                ['field' => 'memberships_count', 'label' => 'Applications'],
                ['field' => 'is_active', 'label' => 'Open', 'type' => 'boolean'],
            ],
            'fields' => [
                ['name' => 'name_en', 'label' => 'Name (English)', 'type' => 'text', 'rules' => 'required|string|max:255', 'width' => 'half'],
                ['name' => 'name_np', 'label' => 'Name (Nepali)', 'type' => 'text', 'rules' => 'required|string|max:255', 'width' => 'half', 'class' => 'np'],
                ['name' => 'duration_unit', 'label' => 'Validity', 'type' => 'select', 'options' => ['years' => 'Years', 'months' => 'Months', 'lifetime' => 'Lifetime (never expires)'], 'rules' => 'required|in:years,months,lifetime', 'width' => 'half', 'default' => 'years', 'help' => 'The plan starts when the application is approved.'],
                ['name' => 'duration_value', 'label' => 'How many years / months', 'type' => 'number', 'rules' => 'nullable|required_if:duration_unit,years,months|integer|min:1|max:100', 'width' => 'half', 'help' => 'e.g. 5 for a five year membership. Ignored for Lifetime.'],
                ['name' => 'fee', 'label' => 'Price (Rs.)', 'type' => 'number', 'rules' => 'nullable|numeric|min:0', 'width' => 'half', 'step' => '0.01', 'help' => 'Leave empty or 0 to make this membership free. With a price, the applicant must upload a payment voucher.'],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'rules' => 'nullable|integer|min:0', 'width' => 'half', 'default' => 0],
                ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'rules' => 'nullable|string|max:1000', 'help' => 'Shown to applicants on the membership page.'],
                ['name' => 'is_active', 'label' => 'Open for new applications', 'type' => 'checkbox', 'default' => true],
            ],
        ],
        // ---------------------------------------------------------------- Donations
        'donations' => [
            'group' => 'donation',
            'label' => 'Donations',
            'singular' => 'Donation',
            'icon' => '💰',
            'model' => Donation::class,
            'controller' => DonationController::class,
            // Members add donations from "My Donations"; admins approve them or return them with a note.
            'approvable' => true,
            'reject_label' => 'Return',
            'rejected_label' => 'Returned',
            'reject_reason' => true,
            'order' => ['donate_date', 'desc'],
            'summary' => ['label' => 'Total Donation Collected', 'sum' => 'amount', 'scope' => 'approved'],
            'columns' => [
                ['field' => 'donor_image_url', 'label' => 'Photo', 'type' => 'image'],
                ['field' => 'donor_name', 'label' => 'Donor'],
                ['field' => 'amount', 'label' => 'Amount', 'type' => 'money'],
                ['field' => 'address', 'label' => 'Address'],
                ['field' => 'donate_date', 'label' => 'Date', 'type' => 'date'],
                ['field' => 'voucher_url', 'label' => 'Voucher', 'type' => 'image', 'link' => true],
                ['field' => 'user.name', 'label' => 'Added By'],
                ['field' => 'status', 'label' => 'Status', 'type' => 'status'],
            ],
            'fields' => [
                ['name' => 'donor_name', 'label' => 'Donor Name', 'type' => 'text', 'rules' => 'required|string|max:255', 'width' => 'half'],
                ['name' => 'amount', 'label' => 'Amount (Rs.)', 'type' => 'number', 'rules' => 'required|numeric|min:1', 'width' => 'half', 'step' => '0.01'],
                ['name' => 'address', 'label' => 'Address', 'type' => 'text', 'rules' => 'nullable|string|max:255', 'width' => 'half'],
                ['name' => 'donate_date', 'label' => 'Donation Date', 'type' => 'date', 'rules' => 'required|date', 'width' => 'half', 'default' => 'today'],
                ['name' => 'donor_image_url', 'label' => 'Donor Photo', 'type' => 'image', 'folder' => 'donations', 'width' => 'half'],
                // Required when a member adds a donation (see MyDonationController).
                ['name' => 'voucher_url', 'label' => 'Paid Bank Voucher', 'type' => 'image', 'folder' => 'donation-vouchers', 'max' => 6144, 'width' => 'half', 'help' => 'Photo or scan of the bank deposit voucher / payment receipt.'],
            ],
        ],

        // ---------------------------------------------------------------- Accounting
        'accounting-categories' => [
            'group' => 'accounting',
            'label' => 'Entry Categories',
            'singular' => 'Category',
            'icon' => '🏷️',
            'model' => AccountingCategory::class,
            'controller' => AccountingCategoryController::class,
            'order' => ['sort_order', 'asc'],
            'columns' => [
                ['field' => 'name', 'label' => 'Category'],
                ['field' => 'type', 'label' => 'Type', 'class' => 'capitalize'],
                ['field' => 'sort_order', 'label' => 'Order'],
                ['field' => 'is_active', 'label' => 'Active', 'type' => 'boolean'],
            ],
            'fields' => [
                ['name' => 'name', 'label' => 'Category Name', 'type' => 'text', 'rules' => 'required|string|max:100', 'width' => 'half'],
                ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'rules' => 'required|in:income,expense', 'width' => 'half', 'options' => ['income' => 'Income', 'expense' => 'Expense'], 'default' => 'income'],
                ['name' => 'sort_order', 'label' => 'Display Order', 'type' => 'number', 'rules' => 'nullable|integer|min:0', 'width' => 'half', 'default' => 0, 'help' => 'Smaller numbers are listed first.'],
                ['name' => 'is_active', 'label' => 'Active (shown when adding an entry)', 'type' => 'checkbox', 'default' => true],
            ],
        ],

        // ---------------------------------------------------------------- Inbox
        'messages' => [
            'group' => 'inbox',
            'label' => 'Contact Messages',
            'singular' => 'Message',
            'icon' => '✉️',
            'model' => ContactMessage::class,
            'order' => ['id', 'desc'],
            'readonly' => true,
            'columns' => [
                ['field' => 'name', 'label' => 'Name'],
                ['field' => 'email', 'label' => 'Email'],
                ['field' => 'subject', 'label' => 'Subject'],
                ['field' => 'message', 'label' => 'Message', 'limit' => 60],
                ['field' => 'created_at', 'label' => 'Received', 'type' => 'datetime'],
            ],
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text'],
                ['name' => 'email', 'label' => 'Email', 'type' => 'text'],
                ['name' => 'subject', 'label' => 'Subject', 'type' => 'text'],
                ['name' => 'created_at', 'label' => 'Received', 'type' => 'datetime'],
                ['name' => 'message', 'label' => 'Message', 'type' => 'textarea'],
            ],
        ],

        // ---------------------------------------------------------------- Users
        'users' => [
            'group' => 'access',
            'label' => 'Users',
            'singular' => 'User',
            'icon' => '👥',
            'model' => User::class,
            'controller' => UserController::class,
            'order' => ['id', 'desc'],
            'columns' => [
                ['field' => 'name', 'label' => 'Name'],
                ['field' => 'email', 'label' => 'Email'],
                ['field' => 'roles', 'label' => 'Role', 'type' => 'roles'],
                ['field' => 'created_at', 'label' => 'Joined', 'type' => 'date'],
            ],
            'fields' => [
                ['name' => 'name', 'label' => 'Full Name', 'type' => 'text', 'width' => 'half'],
                ['name' => 'email', 'label' => 'Email', 'type' => 'text', 'width' => 'half'],
                ['name' => 'role', 'label' => 'Role', 'type' => 'select', 'options_from' => Role::class, 'width' => 'half', 'default' => 'user', 'help' => 'Choose what this person may do. Manage roles under Roles & Permissions.'],
                ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'width' => 'half', 'help' => 'Leave blank to keep the current password.'],
            ],
        ],
    ],
];
