<?php
include("database.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered List</title>
    <link rel="stylesheet" href="register.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <style>
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.5em 1em;
            margin: 0 2px;
            border: 1px solid #ddd;
            background: linear-gradient(to bottom, #fff 0%, #f5f5f5 100%);
            color: #333 !important;
            border-radius: 4px;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(to bottom, #ffcc00 0%, #e6b800 100%);
            color: white !important;
            border: 1px solid #e6b800;
        }
        .dataTables_wrapper .dataTables_filter input {
            padding: 8px;
            border: 2px solid #ddd;
            border-radius: 4px;
        }
    </style>
    <script>
        $(document).ready(function() {
            $('#myTable').DataTable({
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search records...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <h1>Registered List</h1>
        <table id="myTable" class="display">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Name</th>
                    <th>Destination</th>
                    <th>Alamat</th>
                    <th>No. Kenderaan</th>
                    <th>Gambar IC</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $sql = "SELECT * FROM registeredlist ORDER BY id DESC";
                    $stmt = $pdo->query($sql);
                    $num = 1;
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                ?>
                        <tr>
                            <td><?= htmlspecialchars($num) ?></td>
                            <td><?= htmlspecialchars($row["name"]) ?></td>
                            <td><?= htmlspecialchars($row["destination"]) ?></td>
                            <td><?= htmlspecialchars($row["alamat"]) ?></td>
                            <td><?= htmlspecialchars($row["noKenderaan"]) ?></td>
                            <td>
                                <a href="<?= htmlspecialchars($row["gambarDir"]) ?>" target="_blank" rel="noopener noreferrer">
                                    View Document
                                </a>
                            </td>
                        </tr>
                <?php
                        $num++;
                    }
                } catch (PDOException $e) {
                    echo "<tr><td colspan='6'>Error loading data: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>