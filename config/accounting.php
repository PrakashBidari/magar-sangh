<?php

/*
|--------------------------------------------------------------------------
| Accounting lists
|--------------------------------------------------------------------------
|
| Categories are managed in the dashboard (Accounting → Entry Categories);
| the lists below are only the starting set copied into the database when
| the accounting_categories table is created, and are used by the factory.
| Payment methods are offered on the income / expense form and the filters.
|
*/

return [
    'categories' => [
        'income' => ['Sales', 'Membership Fee', 'Donation', 'Grant', 'Event Income', 'Interest', 'Other Income'],
        'expense' => ['Salary', 'Rent', 'Food', 'Transport', 'Utilities', 'Office Supplies', 'Printing', 'Event Expense', 'Repair', 'Other Expense'],
    ],

    'payment_methods' => ['Cash', 'Bank', 'eSewa', 'Khalti', 'Cheque', 'Other'],
];
