# Partial Payment Feature Instructions

## Overview
The Partial Payment feature allows the finance team to process payments for Purchase Requisitions in installments. Instead of paying the full approved amount at once, users can now specify a partial amount to pay. The system tracks the remaining balance and allows for subsequent payments until the full amount is settled.

## How to Use

### 1. Initiating a Partial Payment
1.  Navigate to **Purchase Requisitions > Payments**.
2.  In the **"New Payments"** tab, you will see a list of approved requisitions pending payment.
3.  Select the requisitions you wish to pay by checking the checkboxes.
4.  Click the **"Process Bulk Payment"** button.
5.  A modal will appear listing the selected items.
6.  For each item, you can toggle between **"Full Payment"** and **"Partial Payment"**.
    *   **Full Payment**: Pays the total approved cost.
    *   **Partial Payment**: Enable the input field to enter the specific amount you wish to pay now.
7.  Click **"Confirm Payment"** to process the batch.
8.  The requisition status will update to **"Partially Paid"** (if not fully paid).

### 2. Paying Remaining Balance
1.  Navigate to **Purchase Requisitions > Payments**.
2.  Switch to the **"Partially Paid"** tab.
3.  This tab lists all requisitions that have an outstanding balance.
    *   **Total Cost**: The original approved amount.
    *   **Paid Amount**: Total amount paid so far across all batches.
    *   **Remaining Balance**: The amount left to pay.
4.  Select the requisitions you wish to pay.
5.  Click **"Process Balance Payment"**.
6.  The modal will appear with the **Remaining Balance** pre-filled as the payment amount.
7.  You can confirm to pay the full remaining balance or enter a smaller amount to continue paying in installments.
8.  Once the total paid equals the total approved cost, the requisition status automatically updates to **"Paid"** and moves to history.

### 3. Viewing Payment Details (Payslip)
1.  Click on a **Batch ID** or usage the **View** icon in the Payment Batches history.
2.  In the Payslip view, you will see the breakdown for each item in that specific batch.
3.  The table shows:
    *   **Approved Amount**: Total cost of the requisition.
    *   **Paying Amount**: The amount paid **in this specific batch**.
    *   **Balance**: The remaining balance after accounting for **all** payments made to date (sum of all batches).

### 4. Reports
1.  Navigate to **Purchase Requisitions > Report**.
2.  The stat cards now include a **"Partially Paid"** count.
3.  The **"Payment Status"** filter now includes a **"Partially Paid"** option.
4.  The "Paid Total" statistic at the top includes the sum of all partial payments made.
5.  The "Unpaid Total" statistic includes the outstanding remaining balances of partially paid items.
6.  In the results table, partially paid items are clearly marked with a **Blue "Partially Paid"** badge.

## Technical Details

### Database Changes
*   **Purchase Requisition Model**:
    *   Use `paymentBatches` relationship (Many-to-Many via `purchase_requisition_payment_batch_items`).
    *   Added `txn_status`, `txn_ref`, `txn_description` to `$fillable`.
*   **Pivot Table**: Stores the specific `amount` paid for each batch transaction.

### Status Codes (`payment_status`)
*   `1`: Pending (Approved but not yet queued for payment)
*   `2`: Unpaid (Queued in a batch / Processing)
*   `3`: **Partially Paid**
*   `4`: Paid (Fully settled)
*   `5`: Failed

### Logic
*   **Balance Calculation**: `Remaining Balance = Total Approved Cost - Sum(All Pivot Amounts for this PR)`.
*   **Status Update**: When confirming a batch, if `Total Paid >= Total Approved Cost`, status sets to `4` (Paid). Otherwise, it sets to `3` (Partially Paid).
