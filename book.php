<?php
session_start(); // Memulai session untuk menyimpan data buku

class Book {
    private $codeBook;
    private $name;
    private $qty;

    // Constructor untuk inisialisasi buku
    public function __construct($codeBook, $name, $qty) {
        $this->setCodeBook($codeBook);
        $this->name = $name;
        $this->setQty($qty);
    }

    // Setter untuk codeBook dengan validasi format "BB00"
    private function setCodeBook($codeBook) {
        if (preg_match('/^[A-Z]{2}\d{2}$/', $codeBook)) {
            $this->codeBook = $codeBook;
            return true; // Set berhasil
        } else {
            return "Format kode tidak valid"; // Tampilkan pesan kesalahan jika format kode tidak valid
        }
    }

    // Setter untuk qty dengan validasi angka positif
    private function setQty($qty) {
        $qty = intval($qty);
        if ($qty > 0) {
            $this->qty = $qty;
            return true; // Set berhasil
        } else {
            return "Jumlah buku harus berupa bilangan bulat positif"; // Tampilkan pesan kesalahan jika kuantitas tidak valid
        }
    }

    // Getter untuk codeBook
    public function getCodeBook() {
        return $this->codeBook;
    }

    // Getter untuk name
    public function getName() {
        return $this->name;
    }

    // Getter untuk qty
    public function getQty() {
        return $this->qty;
    }
}

// Inisialisasi session jika belum ada
if (!isset($_SESSION['books'])) {
    $_SESSION['books'] = [];
}

// Deklarasi variabel untuk pesan kesalahan
$error = "";

// Menambahkan buku baru ke dalam session jika ada data yang dikirimkan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codeBook = $_POST["code"];
    $name = $_POST["name"];
    $qty = $_POST["quantity"];

    // Buat objek buku baru
    $result = addBook($codeBook, $name, $qty);

    // Jika gagal menambahkan buku, tangkap pesan kesalahan
    if (!$result) {
        if ($codeBookError = validateCodeBook($codeBook)) {
            // Jika terdapat kesalahan pada format kode
            $error = "Gagal Menambahkan Buku: $codeBookError";
        } elseif ($qtyError = validateQty($qty)) {
            // Jika terdapat kesalahan pada jumlah buku
            $error = "Gagal Menambahkan Buku: $qtyError";
        } else {
            // Kesalahan umum jika tidak ada validasi yang cocok
            $error = "Gagal Menambahkan Buku. Silakan periksa kembali informasi yang Anda masukkan.";
        }
    }
}

// Fungsi validasi format kode buku
function validateCodeBook($codeBook) {
    if (!preg_match('/^[A-Z]{2}\d{2}$/', $codeBook)) {
        return "Format kode tidak valid";
    }
    return false;
}

// Fungsi validasi jumlah buku
function validateQty($qty) {
    $qty = intval($qty);
    if ($qty <= 0) {
        return "Jumlah buku harus berupa bilangan bulat positif";
    }
    return false;
}

// Fungsi untuk menambahkan buku dan melakukan validasi
function addBook($codeBook, $name, $qty) {
    $book = new Book($codeBook, $name, $qty);

    // Jika berhasil, tambahkan buku ke dalam session
    if ($book->getCodeBook() && $book->getName() && $book->getQty()) {
        $_SESSION['books'][] = $book;
        return true;
    } else {
        return false;
    }
}

// Hapus buku dari session jika ada data yang dikirimkan
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["delete_code"])) {
    $deleteCode = $_GET["delete_code"];
    // Cari indeks buku yang akan dihapus
    foreach ($_SESSION['books'] as $index => $book) {
        if ($book->getCodeBook() === $deleteCode) {
            unset($_SESSION['books'][$index]);
            // Keluar dari loop setelah buku dihapus
            break;
        }
    }
    // Reset kembali indeks array untuk mencegah indeks yang kosong
    $_SESSION['books'] = array_values($_SESSION['books']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
<div class="container">
    <div class="content">
        <div class="add-book">
            <h2>Tambah Buku</h2>

            <!-- Form tambah buku -->
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="text" name="code" placeholder="Masukkan Kode Buku (cth. BB01)">
                <input type="text" name="name" placeholder="Masukkan Nama Buku">
                <input type="number" name="quantity" placeholder="Masukkan Jumlah Buku">
                <button type="submit">Tambah Buku</button>
            </form>
            <br>
            <!-- Pindahkan pesan kesalahan ke sini -->
            <?php if ($error): ?>
                <p class="error-message"><?php echo $error; ?></p>
            <?php endif; ?>
        </div>
        <div class="book-list">
            <h2>Daftar Buku</h2>
            <!-- Tampilkan daftar buku -->
            <table>
                <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Jumlah</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($_SESSION['books'] as $book): ?>
                    <tr>
                        <td><?php echo $book->getCodeBook(); ?></td>
                        <td><?php echo $book->getName(); ?></td>
                        <td><?php echo $book->getQty(); ?></td>
                        <td>
                            <form method="get" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
                                <input type="hidden" name="delete_code" value="<?php echo $book->getCodeBook(); ?>">
                                <button class="delete-button" type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
