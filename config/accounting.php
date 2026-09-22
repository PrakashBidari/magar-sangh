<?php

/*
|--------------------------------------------------------------------------
| Accounting lists
|--------------------------------------------------------------------------
|
| Choices offered on the income / expense form and the filter checkboxes.
| Values are stored as plain text, so entries keep their category even if a
| name is later removed from these lists.
|
*/

return [
    'categories' => [
        'income' => ['Sales', 'Membership Fee', 'Donation', 'Grant', 'Event Income', 'Interest', 'Other Income'],
        'expense' => ['Salary', 'Rent', 'Food', 'Transport', 'Utilities', 'Office Supplies', 'Printing', 'Event Expense', 'Repair', 'Other Expense'],
    ],

    'payment_methods' => ['Cash', 'Bank', 'eSewa', 'Khalti', 'Cheque', 'Other'],
];
