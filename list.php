<!DOCTYPE html>
<html lang="en">
<?php
    include("database.php");
    if(isset($_POST['saveButton'])){
        $idValue = $_POST['idValue'];
        $noahli = $_POST['noahli'];
        $norumah = $_POST['norumah'];
        $jalan = $_POST['jalan'];
        $name = $_POST['name'];
        $kadinduk1 = $_POST['kadinduk1'];
        $kadinduk2 = $_POST['kadinduk2'];
        $nohp1 = $_POST['nohp1'];
        $nohp2 = $_POST['nohp2'];
        $email1 = $_POST['email1'];
        $email2 = $_POST['email2'];
        $status = (int)$_POST['status'];

        $sql = "UPDATE residents SET noahli=?, norumah=?, jalan=?, name=?, kadinduk1=?, kadinduk2=?, no_hp1=?, no_hp2=?, email1=?, email2=?, status=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$noahli, $norumah, $jalan, $name, $kadinduk1, $kadinduk2, $nohp1, $nohp2, $email1, $email2, $status, $idValue]);
    }
    
    if(isset($_POST['addButton'])){
        $noahli = $_POST['noahli'];
        $norumah = $_POST['norumah'];
        $jalan = $_POST['jalan'];
        $name = $_POST['name'];
        $kadinduk1 = $_POST['kadinduk1'];
        $kadinduk2 = $_POST['kadinduk2'];
        $nohp1 = $_POST['nohp1'];
        $nohp2 = $_POST['nohp2'];
        $email1 = $_POST['email1'];
        $email2 = $_POST['email2'];
        $status = (int)$_POST['status'];
        $createdDate = date("Y-m-d H:i:s");
        $createdId = 0;

        $sql = "INSERT INTO residents (noahli, norumah, jalan, name, kadinduk1, kadinduk2, no_hp1, no_hp2, email1, email2, status, createdDate, createdId) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$noahli, $norumah, $jalan, $name, $kadinduk1, $kadinduk2, $nohp1, $nohp2, $email1, $email2, $status, $createdDate, $createdId]);
    }
    
    if(isset($_POST['deleteButtonReal'])){
        $theId = $_POST['realId'];
        $sql = "UPDATE residents SET status=0 WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$theId]);

    }
    
    if(isset($_POST['restoreButtonReal'])){
        $theId = $_POST['realId2'];
        $sql = "UPDATE residents SET status=1 WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$theId]);

    }
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Residents</title>
    <link rel="stylesheet" href="list.css">
    <!-- Include DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  
    <!-- Include jQuery (required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Include DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <!-- Tooltip link -->
     <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
        $(document).ready(function() {
            $('#myTable').DataTable({
                paging: true, // Enable pagination
                searching: true, // Enable search bar
                ordering: true, // Enable column sorting
                info: true, // Show table information
                lengthChange: true, // Allow changing the number of records per page
                pageLength: 10, // Default number of rows per page
                responsive: true // Enable responsive design
            });
            $('#theTable').DataTable({
                paging: true, // Enable pagination
                searching: true, // Enable search bar
                ordering: true, // Enable column sorting
                info: true, // Show table information
                lengthChange: true, // Allow changing the number of records per page
                pageLength: 10, // Default number of rows per page
                responsive: true // Enable responsive design
            });
        });
    </script>
</head>
<body>
    <div class="navBar" id="navBar">
        <img onclick="location.reload()" style="cursor:pointer;" class="logo" src="logoSeksyen9.png" width="50" height="50">
        <h1  onclick="location.reload()" style="cursor:pointer;" >Seksyen 9 Residents</h1>
        <div class="navButtons">
            <a class="navButton active" title="Active Page" href="#"><i class="fa-solid fa-users"></i>Active</a>
            <a class="navButton" title="Deleted Page" href="#"><i class="fa-solid fa-users-slash"></i>Deleted</a>
        </div>
    </div>
    <div class="container">
        <div id="activePage">
            <button id="insertButton"><i class="fa-solid fa-user-plus"></i><span class="insertText" >Insert</span></button>
            <table id="myTable" class="display">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>No. Ahli</th>
                        <th>No. Rumah</th>
                        <th>Jalan</th>
                        <th>Name</th>
                        <th>Kad Induk 1</th>
                        <th>Kad Induk 2</th>
                        <th>No. HP 1</th>
                        <th>No. HP 2</th>
                        <th>Email 1</th>
                        <th>Email 2</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th>Created Id</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        // Using PDO connection ($pdo instead of $conn)
                        $sql = "SELECT * FROM residents WHERE status != 0"; 
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();
                        $residents = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $num = 1;

                        foreach ($residents as $row) { 
                    ?>
                    <tr>
                        <td data-id="<?= htmlspecialchars($row["id"]); ?>"><?= $num; ?></td>
                        <td><?= htmlspecialchars($row["noahli"]); ?></td>
                        <td><?= htmlspecialchars($row["norumah"]); ?></td>
                        <td><?= htmlspecialchars($row["jalan"]); ?></td>
                        <td><?= htmlspecialchars($row["name"]); ?></td>
                        <td><?= htmlspecialchars($row["kadinduk1"]); ?></td>
                        <td><?= htmlspecialchars($row["kadinduk2"]); ?></td>
                        <td><?= htmlspecialchars($row["no_hp1"]); ?></td>
                        <td><?= htmlspecialchars($row["no_hp2"]); ?></td>
                        <td><?= htmlspecialchars($row["email1"]); ?></td>
                        <td><?= htmlspecialchars($row["email2"]); ?></td>
                        <td><?= $row["status"] == 1 ? "Belum Ambil" : "Dah Ambil"; ?></td>
                        <td><?= htmlspecialchars($row["createdDate"]); ?></td>
                        <td><?= htmlspecialchars($row["createdId"]); ?></td>
                        <td>
                            <button class="updateButton" title="Edit user"><i class="fa-solid fa-pen-to-square"></i>Edit</button>
                            <button class="deleteButton" title="Delete user"><i class="fa-solid fa-trash"></i>Delete</button>
                        </td>
                    </tr>
                    <?php
                            $num++;
                        }
                    ?>
                </tbody>
            </table>

        </div>
        <div id="deletedPage" style="display:none;">
        <table id="theTable" class="display">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>No. Ahli</th>
                    <th>No. Rumah</th>
                    <th>Jalan</th>
                    <th>Name</th>
                    <th>Kad Induk 1</th>
                    <th>Kad Induk 2</th>
                    <th>No. HP 1</th>
                    <th>No. HP 2</th>
                    <th>Email 1</th>
                    <th>Email 2</th>
                    <th>Created Date</th>
                    <th>Created Id</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    // Using PDO connection ($pdo instead of $conn)
                    $sql = "SELECT * FROM residents WHERE status = 0"; 
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    $residents = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $num2 = 1;

                    foreach ($residents as $row) { 
                ?>
                <tr>
                    <td data-id="<?= htmlspecialchars($row["id"]); ?>"><?= $num2; ?></td>
                    <td><?= htmlspecialchars($row["noahli"]); ?></td>
                    <td><?= htmlspecialchars($row["norumah"]); ?></td>
                    <td><?= htmlspecialchars($row["jalan"]); ?></td>
                    <td><?= htmlspecialchars($row["name"]); ?></td>
                    <td><?= htmlspecialchars($row["kadinduk1"]); ?></td>
                    <td><?= htmlspecialchars($row["kadinduk2"]); ?></td>
                    <td><?= htmlspecialchars($row["no_hp1"]); ?></td>
                    <td><?= htmlspecialchars($row["no_hp2"]); ?></td>
                    <td><?= htmlspecialchars($row["email1"]); ?></td>
                    <td><?= htmlspecialchars($row["email2"]); ?></td>
                    <td><?= htmlspecialchars($row["createdDate"]); ?></td>
                    <td><?= htmlspecialchars($row["createdId"]); ?></td>
                    <td>
                        <button class="restoreButton" title="Restore User"><i class="fa-solid fa-notes-medical"></i>Restore</button>
                    </td>
                </tr>
                <?php
                        $num2++;
                    }
                ?>
            </tbody>
        </table>

        </div>
    </div>
    <div class="footer">
        <div class="followBox">
            <p>Follow me:</p>
            <a href="https://www.instagram.com/hazeeeim/"  target="_blank"><i class="fa-brands fa-instagram"></i></a>
            <a href="https://www.facebook.com/nufailhazim.sarif"  target="_blank"><i class="fa-brands fa-facebook"></i></a>
            <a href="https://www.youtube.com/@nufailhazim2048" target="_blank"><i class="fa-brands fa-youtube"></i></a>
            <a href="https://github.com/NuHazim" target="_blank"><i class="fa-brands fa-github"></i></a>
        </div>
        <hr>
        <div class="contactBox">
                    <p>Contact me:</p>
                    <p><i class="fa-brands fa-whatsapp"></i>+6011-5754-8004</p>
        </div>
    </div>
        </div>
    </div>
    <dialog id="theDialog">
        <form id="myForm" action="list.php" method="post">
            <div class="minibox"><label id="Id"></label><input type="number" style="display:none;" name="idValue" id="idValue"></div>
            <div class="minibox"><label>No. Ahli: </label><input id="noahli" name="noahli" type="text"></div>
            <div class="minibox"><label>No. Rumah: </label><input id="norumah" name="norumah" type="text"></div>
            <div class="minibox"><label>Jalan: </label><input id="jalan" name="jalan" type="text"></div>
            <div class="minibox"><label>Name: </label><input id="name" name="name" type="text"></div>
            <div class="minibox"><label>Kad Induk 1: </label><input id="kadinduk1" name="kadinduk1" type="text"></div>
            <div class="minibox"><label>Kad Induk 2: </label><input id="kadinduk2" name="kadinduk2" type="text"></div>
            <div class="minibox"><label>No. HP 1: </label><input id="nohp1" name="nohp1" type="text"></div>
            <div class="minibox"><label>No. HP 2: </label><input id="nohp2" name="nohp2" type="text"></div>
            <div class="minibox"><label>Email 1: </label><input id="email1" name="email1" type="text"></div>
            <div class="minibox"><label>Email 2: </label><input id="email2" name="email2" type="text"></div>
            <div class="minibox"><label>Status: </label><select id="status" name="status"><option value="1">Belum Ambil</option><option value="2">Dah Ambil</option></select></div>
            <div class="minibox"><label name="createdDate" id="createdDate"></label></div>
            <div class="minibox"><label name="createdId" id="createdId"></label></div>
            <div class="minibox">
                <button type="submit" id="saveButton" name="saveButton">Save</button>
                <button id="addButton" name="addButton" type="submit">Add</button>
                <button id="closeDialog" class="cancelButton" style="margin-left:20px;" type="button">cancel</button>
            </div>
            <button type="button" class="axe" id="axeButton"><i class="fa-solid fa-xmark"></i></button>
        </form>
    </dialog>
    <dialog id="theDialog2">
        <form action="list.php" method="post">
            <h2>Are you sure deleting row:</h2>
            <label id="fakeId"></label><input id="realId" name="realId" type="number" style="display:none;">
            <div>
                <button type="submit" class="deleteButtonReal" name="deleteButtonReal">Delete</button>
                <button type="button" class="cancelButton" id="closeDialog2">cancel</button>
            </div>
            <button type="button" class="axe" id="axeButton2"><i class="fa-solid fa-xmark"></i></button>
        </form>
    </dialog>
    <dialog id="theDialog3">
        <form action="list.php" method="post">
            <h2>Restoring row:</h2>
            <label id="fakeId2"></label><input id="realId2" name="realId2" type="number" style="display:none;">
            <div>
                <button type="submit" class="restoreButtonReal" name="restoreButtonReal">restore</button>
                <button type="button" class="cancelButton" id="closeDialog3">cancel</button>
            </div>
            <button type="button" class="axe" id="axeButton3"><i class="fa-solid fa-xmark"></i></button>
        </form>
    </dialog>
    <script>
        const navbar = document.getElementById("navBar");
        let lastScrollY= window.scrollY;
        window.addEventListener("scroll", () => {
            if (window.scrollY > lastScrollY) {
                navbar.style.transform="translateY(-100%)";
            } else {
                navbar.style.transform="none";
            }
            lastScrollY = window.scrollY;
        });
        const activePage=document.getElementById("activePage");
        const deletedPage=document.getElementById("deletedPage");
        const navButtons=document.querySelectorAll(".navButton");
        navButtons[0].addEventListener("click",function(e){
            e.preventDefault();
            activePage.style.display="inline";
            deletedPage.style.display="none";
            navButtons[0].classList.add("active");
            navButtons[1].classList.remove("active");
        })
        navButtons[1].addEventListener("click",function(e){
            e.preventDefault();
            activePage.style.display="none";
            deletedPage.style.display="inline";
            navButtons[0].classList.remove("active");
            navButtons[1].classList.add("active");
        })
        const fakeId=document.getElementById("fakeId");
        const realId=document.getElementById("realId");
        const fakeId2=document.getElementById("fakeId2");
        const realId2=document.getElementById("realId2");
        const idValue=document.getElementById("idValue");
        const updateId = document.getElementById("Id");
        const noahli = document.getElementById("noahli");
        const norumah = document.getElementById("norumah");
        const jalan = document.getElementById("jalan");
        const name = document.getElementById("name");
        const kadinduk1 = document.getElementById("kadinduk1");
        const kadinduk2 = document.getElementById("kadinduk2");
        const nohp1 = document.getElementById("nohp1");
        const nohp2 = document.getElementById("nohp2");
        const email1 = document.getElementById("email1");
        const email2 = document.getElementById("email2");
        const status = document.getElementById("status");
        const theDialog = document.getElementById("theDialog");
        const theDialog2 = document.getElementById("theDialog2");
        const theDialog3 = document.getElementById("theDialog3");
        const closeDialog = document.getElementById("closeDialog");
        const closeDialog2 = document.getElementById("closeDialog2");
        const closeDialog3 = document.getElementById("closeDialog3");
        const addButton=document.getElementById("addButton");
        const insertButton=document.getElementById("insertButton");
        const myForm=document.getElementById("myForm");
        insertButton.addEventListener("click",function(){
            theDialog.showModal();
            addButton.style.display="block";
            saveButton.style.display="none";
            updateId.textContent="Id: <?php echo $num;?>";
            myForm.reset();
        })
        document.getElementById('myTable').addEventListener('click', function(e) {
            if (e.target.classList.contains('deleteButton')||e.target.classList.contains('fa-trash')) {
                theDialog2.showModal();
                const row = e.target.closest('tr');
                let tds = row.querySelectorAll("td");
                realId.value = tds[0].dataset.id;
                fakeId.textContent = "Id: " + tds[0].textContent;
            }
        });
        document.getElementById('myTable').addEventListener('click', function(e) {
            if (e.target.classList.contains('updateButton')||e.target.classList.contains('fa-pen-to-square')) {
                theDialog.showModal();
                addButton.style.display="none";
                saveButton.style.display="block";
                
                const row = e.target.closest('tr');
                let tds = row.querySelectorAll("td");
                idValue.value = tds[0].dataset.id;
                updateId.textContent = "Id: " + tds[0].textContent;
                noahli.value = tds[1].textContent;
                norumah.value = tds[2].textContent;
                jalan.value = tds[3].textContent;
                name.value = tds[4].textContent;
                kadinduk1.value = tds[5].textContent;
                kadinduk2.value = tds[6].textContent;
                nohp1.value = tds[7].textContent;
                nohp2.value = tds[8].textContent;
                email1.value = tds[9].textContent;
                email2.value = tds[10].textContent;
                status.value = tds[11].textContent == "Belum Ambil" ? "1" : "2";
            }
        });
        document.getElementById("theTable").addEventListener("click",function(e){
            if(e.target.classList.contains("restoreButton")||e.target.classList.contains("fa-notes-medical")){
                const theRow=e.target.closest("tr");
                const columns=theRow.querySelectorAll("td");
                realId2.value=columns[0].dataset.id;
                fakeId2.textContent="Id: "+columns[0].textContent;
                theDialog3.showModal();
            }
        })
        function closeD(){
            theDialog.close();
        }
        function closeD2(){
            theDialog2.close();
        }
        function closeD3(){
            theDialog3.close();
        }
        closeDialog.addEventListener("click", closeD);
        const axeButton=document.getElementById("axeButton");
        axeButton.addEventListener("click",closeD);
        closeDialog2.addEventListener("click",closeD2);
        const axeButton2=document.getElementById("axeButton2");
        axeButton2.addEventListener("click",closeD2);
        closeDialog3.addEventListener("click", closeD3);
        const axeButton3=document.getElementById("axeButton3");
        axeButton3.addEventListener("click",closeD3);
    </script>
</body>
</html>

