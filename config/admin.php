<?php

use App\Http\Controllers\Admin\UserController;
use App\Models\Article;
use App\Models\CommitteeMember;
use App\Models\ContactMessage;
use App\Models\Donation;
use App\Models\Event;
use App\Models\GalleryPhoto;
use App\Models\GalleryVideo;
use App\Models\HeroSlide;
use App\Models\News;
use App\Models\NotificationItem;
use App\Models\Publication;
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
        'donation' => 'Donations',
        'inbox' => 'Inbox',
        'people' => 'People',
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
            'order' => ['published_at', 'desc'],
            'columns' => [
                ['field' => 'image_url', 'label' => 'Image', 'type' => 'image'],
                ['field' => 'title', 'label' => 'Title'],
                ['field' => 'published_at', 'label' => 'Published', 'type' => 'datetime'],
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
                ['field' => 'title', 'label' => 'Title'],
                ['field' => 'published_at', 'label' => 'Published', 'type' => 'datetime'],
            ],
            'fields' => [
                ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => 'required|string|max:255'],
                ['name' => 'slug', 'label' => 'Slug (URL)', 'type' => 'text', 'rules' => 'nullable|string|max:255', 'help' => 'Leave blank to generate from the title.', 'width' => 'half'],
                ['name' => 'published_at', 'label' => 'Publish Date & Time', 'type' => 'datetime', 'rules' => 'nullable|date', 'width' => 'half', 'default' => 'now'],
                ['name' => 'body', 'label' => 'Description', 'type' => 'richtext', 'rules' => 'nullable|string'],
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
        'committee' => [
            'group' => 'organization',
            'label' => 'Committee & Presidents',
            'singular' => 'Member',
            'icon' => '🏛️',
            'model' => CommitteeMember::class,
            'order' => ['sort_order', 'asc'],
            'columns' => [
                ['field' => 'photo_url', 'label' => 'Photo', 'type' => 'image'],
                ['field' => 'name', 'label' => 'Name'],
                ['field' => 'position_np', 'label' => 'Position'],
                ['field' => 'term_label', 'label' => 'Term'],
                ['field' => 'is_current', 'label' => 'Current', 'type' => 'boolean'],
                ['field' => 'is_past_president', 'label' => 'Past President', 'type' => 'boolean'],
                ['field' => 'sort_order', 'label' => 'Order'],
            ],
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:255'],
                ['name' => 'photo_url', 'label' => 'Photo', 'type' => 'image', 'folder' => 'committee'],
                ['name' => 'position_np', 'label' => 'Position (Nepali)', 'type' => 'text', 'rules' => 'required|string|max:255', 'width' => 'half', 'class' => 'np'],
                ['name' => 'position_en', 'label' => 'Position (English)', 'type' => 'text', 'rules' => 'nullable|string|max:255', 'width' => 'half'],
                ['name' => 'term_label', 'label' => 'Term Label', 'type' => 'text', 'rules' => 'nullable|string|max:255', 'help' => 'e.g. 2078 – 2082', 'width' => 'half', 'class' => 'np'],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'rules' => 'nullable|integer|min:0', 'width' => 'half', 'default' => 0],
                ['name' => 'term_start', 'label' => 'Term Start', 'type' => 'date', 'rules' => 'nullable|date', 'width' => 'half'],
                ['name' => 'term_end', 'label' => 'Term End', 'type' => 'date', 'rules' => 'nullable|date|after_or_equal:term_start', 'width' => 'half'],
                ['name' => 'is_current', 'label' => 'Part of the current committee', 'type' => 'checkbox'],
                ['name' => 'is_past_president', 'label' => 'Is a past president', 'type' => 'checkbox'],
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
                ['field' => 'logo_url', 'label' => 'Logo', 'type' => 'image'],
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

        // ---------------------------------------------------------------- Donations
        'donations' => [
            'group' => 'donation',
            'label' => 'Lakhan Thapa Pratisthan',
            'singular' => 'Donation',
            'icon' => '💰',
            'model' => Donation::class,
            'order' => ['donate_date', 'desc'],
            'columns' => [
                ['field' => 'donor_image_url', 'label' => 'Photo', 'type' => 'image'],
                ['field' => 'donor_name', 'label' => 'Donor'],
                ['field' => 'amount', 'label' => 'Amount', 'type' => 'money'],
                ['field' => 'address', 'label' => 'Address'],
                ['field' => 'donate_date', 'label' => 'Date', 'type' => 'date'],
            ],
            'fields' => [
                ['name' => 'donor_name', 'label' => 'Donor Name', 'type' => 'text', 'rules' => 'required|string|max:255', 'width' => 'half'],
                ['name' => 'amount', 'label' => 'Amount (Rs.)', 'type' => 'number', 'rules' => 'required|numeric|min:1', 'width' => 'half', 'step' => '0.01'],
                ['name' => 'address', 'label' => 'Address', 'type' => 'text', 'rules' => 'nullable|string|max:255', 'width' => 'half'],
                ['name' => 'donate_date', 'label' => 'Donation Date', 'type' => 'date', 'rules' => 'required|date', 'width' => 'half', 'default' => 'today'],
                ['name' => 'donor_image_url', 'label' => 'Donor Photo', 'type' => 'image', 'folder' => 'donations'],
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
            'group' => 'people',
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
                ['name' => 'role', 'label' => 'Role', 'type' => 'select', 'options' => ['admin' => 'Admin', 'user' => 'User'], 'width' => 'half', 'default' => 'user'],
                ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'width' => 'half', 'help' => 'Leave blank to keep the current password.'],
            ],
        ],
    ],
];
