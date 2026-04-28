<?php
// Configurazione Database
$conn = new mysqli("localhost", "root", "", "silva_biblioteca");

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Gestione Azioni (Logica dai file salva_*.php e restituisci.php)
$messaggio = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['azione'])) {
        if ($_POST['azione'] === 'salva_libro') {
            $titolo = $_POST['titolo'];
            $anno = $_POST['anno'];
            $isbn = $_POST['isbn'];
            $id_autore = $_POST['id_autore'];
            $conn->query("INSERT INTO Libri (titolo, anno_pubblicazione, isbn, id_autore) VALUES ('$titolo', $anno, '$isbn', $id_autore)");
            $messaggio = "Libro inserito con successo!";
        } 
        elseif ($_POST['azione'] === 'salva_prestito') {
            $id_libro = $_POST['id_libro'];
            $id_utente = $_POST['id_utente'];
            $inizio = $_POST['inizio'];
            $fine = $_POST['fine'];
            $conn->query("INSERT INTO Prestiti (id_libro, id_utente, data_inizio, data_fine_prevista) VALUES ($id_libro, $id_utente, '$inizio', '$fine')");
            $messaggio = "Prestito registrato!";
        }
    }
}

if (isset($_GET['restituisci_id'])) {
    $id_p = $_GET['restituisci_id'];
    $conn->query("UPDATE Prestiti SET restituito = TRUE WHERE id_prestito = $id_p");
    $messaggio = "Libro restituito!";
}

// Recupero Dati per le Select
$autori = $conn->query("SELECT * FROM Autori");
$libri = $conn->query("SELECT * FROM Libri");
$utenti = $conn->query("SELECT * FROM Utenti");
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Biblioteca</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 1000px; margin: auto; }
        header { text-align: center; margin-bottom: 30px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        h2 { border-bottom: 2px solid #3498db; padding-bottom: 10px; color: #2c3e50; margin-top: 0; }
        form { display: flex; flex-direction: column; gap: 10px; }
        input, select, button { padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        button { background-color: #3498db; color: white; border: none; cursor: pointer; transition: background 0.3s; }
        button:hover { background-color: #2980b9; }
        .alert { background-color: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px; text-align: center; }
        .full-width { grid-column: 1 / -1; }
        .loan-item { border-bottom: 1px solid #eee; padding: 10px 0; display: flex; justify-content: space-between; align-items: center; }
        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; }
        .returned { background: #e9ecef; color: #6c757d; }
        .pending { background: #fff3cd; color: #856404; }
        .btn-return { background: #27ae60; text-decoration: none; color: white; padding: 5px 10px; border-radius: 4px; font-size: 12px; }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>📚 Sistema Gestione Biblioteca</h1>
    </header>

    <?php if($messaggio): ?>
        <div class="alert"><?= $messaggio ?></div>
    <?php endif; ?>

    <div class="grid">
        
        <div class="card">
            <h2>Nuovo Libro</h2>
            <form method="POST">
                <input type="hidden" name="azione" value="salva_libro">
                <label>Titolo:</label>
                <input type="text" name="titolo" required>
                <label>Anno:</label>
                <input type="number" name="anno" required>
                <label>ISBN:</label>
                <input type="text" name="isbn" required>
                <label>Autore:</label>
                <select name="id_autore">
                    <?php while($a = $autori->fetch_assoc()): ?>
                        <option value="<?= $a['id_autore'] ?>"><?= $a['nome'] . " " . $a['cognome'] ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit">Aggiungi Libro</button>
            </form>
        </div>

        <div class="card">
            <h2>Nuovo Prestito</h2>
            <form method="POST">
                <input type="hidden" name="azione" value="salva_prestito">
                <label>Libro:</label>
                <select name="id_libro">
                    <?php while($l = $libri->fetch_assoc()): ?>
                        <option value="<?= $l['id_libro'] ?>"><?= $l['titolo'] ?></option>
                    <?php endwhile; ?>
                </select>
                <label>Utente:</label>
                <select name="id_utente">
                    <?php while($u = $utenti->fetch_assoc()): ?>
                        <option value="<?= $u['id_utente'] ?>"><?= $u['nome'] . " " . $u['cognome'] ?></option>
                    <?php endwhile; ?>
                </select>
                <label>Data Inizio:</label>
                <input type="date" name="inizio" required>
                <label>Data Fine Prevista:</label>
                <input type="date" name="fine" required>
                <button type="submit" style="background-color: #e67e22;">Registra Prestito</button>
            </form>
        </div>

        <div class="card full-width">
            <h2>Ricerca Prestiti Utente</h2>
            <form method="GET" style="flex-direction: row; align-items: flex-end;">
                <div style="flex-grow: 1;">
                    <label>Seleziona Utente:</label><br>
                    <select name="id_utente" style="width: 100%;">
                        <?php 
                        $utenti->data_seek(0); // Reset puntatore query
                        while($u = $utenti->fetch_assoc()): ?>
                            <option value="<?= $u['id_utente'] ?>" <?= (isset($_GET['id_utente']) && $_GET['id_utente'] == $u['id_utente']) ? 'selected' : '' ?>>
                                <?= $u['nome'] . " " . $u['cognome'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit">Mostra Elenco</button>
            </form>

            <div style="margin-top: 20px;">
                <?php
                if(isset($_GET['id_utente'])){
                    $id = $_GET['id_utente'];
                    $query = "SELECT p.*, l.titolo FROM Prestiti p 
                              JOIN Libri l ON p.id_libro = l.id_libro 
                              WHERE p.id_utente = $id";
                    $ris = $conn->query($query);

                    if($ris->num_rows > 0){
                        while($row = $ris->fetch_assoc()){
                            echo "<div class='loan-item'>";
                            echo "<span><strong>" . $row['titolo'] . "</strong> (dal " . $row['data_inizio'] . ")</span>";
                            
                            if(!$row['restituito']){
                                echo "<div><span class='status-badge pending'>In corso</span> ";
                                echo "<a href='?id_utente=$id&restituisci_id=".$row['id_prestito']."' class='btn-return'>Restituisci</a></div>";
                            } else {
                                echo "<span class='status-badge returned'>Restituito</span>";
                            }
                            echo "</div>";
                        }
                    } else {
                        echo "<p>Nessun prestito trovato per questo utente.</p>";
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>