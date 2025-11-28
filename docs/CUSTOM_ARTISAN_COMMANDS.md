# Custom Artisan Commands

This document provides a summary of the custom artisan commands available in this project.

## Running Commands

All commands should be executed using the following format:

```bash
docker compose exec app php artisan <command-name> [arguments] [options]
```

---

## Command List

### 1. Analyze Purchase Cycle

Analyzes sales and inventory to recommend a sustainable purchasing cycle and budget.

- **Signature:** `app:analyze-purchase-cycle`
- **Description:** This command analyzes your sales history and current inventory value to provide a strategic recommendation for your purchasing frequency and budget allocation. It helps in optimizing cash flow by preventing overstocking and ensuring you have enough inventory to meet demand.
- **Options:**
    - `--balance=<value>`: Your current cash balance for purchasing. (Default: `3000000`)
    - `--sales-period=<days>`: The historical period in days to analyze for sales velocity. (Default: `30`)
- **Example:**
    ```bash
    docker compose exec app php artisan app:analyze-purchase-cycle --balance=5000000 --sales-period=60
    ```

### 2. Backup Database

Creates a backup of the MySQL database.

- **Signature:** `db:backup`
- **Description:** This command creates a `.sql` dump of the entire database and stores it in the `storage/app/backups` directory. The filename will be timestamped (e.g., `backup-muazara-20251023120000.sql`).
- **Example:**
    ```bash
    docker compose exec app php artisan db:backup
    ```

### 3. Generate Purchase Recommendation

Generates a prioritized purchase recommendation list.

- **Signature:** `app:generate-purchase-recommendation`
- **Description:** This command generates a prioritized list of products to reorder based on sales velocity, current stock levels, and a specified budget. It helps ensure that you restock products that are selling well before they run out.
- **Options:**
    - `--budget=<value>`: The total budget for purchasing. (Default: `1500000`)
    - `--sales-period=<days>`: The period in days to calculate sales velocity. (Default: `30`)
    - `--safety-stock=<days>`: The minimum number of days of stock to keep. (Default: `7`)
    - `--target-stock=<days>`: The target number of days of stock to have after reordering. (Default: `14`)
- **Example:**
    ```bash
    docker compose exec app php artisan app:generate-purchase-recommendation --budget=2000000
    ```

### 4. Report Top Selling Products

Identifies top-selling products with low stock.

- **Signature:** `app:report-top-selling-products`
- **Description:** This command analyzes the top 100 best-selling products of the last month and identifies which of them have a stock level below 5. This is useful for quickly identifying popular products that need immediate restocking.
- **Example:**
    ```bash
    docker compose exec app php artisan app:report-top-selling-products
    ```

### 5. Update Purchase Status

Updates the payment status of purchases for a specific supplier.

- **Signature:** `purchase:update-status {supplier_id}`
- **Description:** This command updates the payment status of all `unpaid` purchases from a specific supplier to `paid`.
- **Arguments:**
    - `supplier_id`: The ID of the supplier.
- **Example:**
    ```bash
    docker compose exec app php artisan purchase:update-status 2
    ```
