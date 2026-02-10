<?php
require __DIR__ . '/db.php';

$action = $_POST['action'] ?? null;

if ($action === 'add') {
    $task = trim($_POST['task'] ?? '');
    if ($task !== '') {
        $stmt = $pdo->prepare('INSERT INTO todos (task) VALUES (:task)');
        $stmt->execute([':task' => $task]);
    }
    header('Location: /');
    exit;
}

if ($action === 'toggle') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare('UPDATE todos SET is_done = 1 - is_done WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
    header('Location: /');
    exit;
}

if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare('DELETE FROM todos WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
    header('Location: /');
    exit;
}

$stmt = $pdo->query('SELECT id, task, is_done, created_at FROM todos ORDER BY id DESC');
$todos = $stmt->fetchAll();
?>
<!doctype html>
  <html lang="en">
    <head>
      <title>Title</title>
      <!-- Required meta tags -->
      <meta charset="utf-8" />
      <meta
        name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no"
      />
  
      <!-- Bootstrap CSS v5.2.1 -->
      <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
        crossorigin="anonymous"
      />
    </head>
  
<body>
  <div class="container">
    <h1>Todo List</h1>

    <form class="add" method="post">
      <input type="hidden" name="action" value="add">
      <input type="text" name="task" placeholder="Add a task..." required>
      <button type="submit">Add</button>
    </form>

    <ul>
      <?php if (count($todos) === 0): ?>
        <li>
          <span class="task">No tasks yet. Add your first one.</span>
        </li>
      <?php endif; ?>

      <?php foreach ($todos as $todo): ?>
        <li>
          <span class="task <?php echo $todo['is_done'] ? 'done' : ''; ?>">
            <?php echo htmlspecialchars($todo['task'], ENT_QUOTES, 'UTF-8'); ?>
          </span>
          <span class="meta">
            <?php echo htmlspecialchars($todo['created_at'], ENT_QUOTES, 'UTF-8'); ?>
          </span>
          <div class="actions">
            <form method="post">
              <input type="hidden" name="action" value="toggle">
              <input type="hidden" name="id" value="<?php echo (int)$todo['id']; ?>">
              <button class="ghost" type="submit">
                <?php echo $todo['is_done'] ? 'Undo' : 'Done'; ?>
              </button>
            </form>
            <form method="post">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?php echo (int)$todo['id']; ?>">
              <button type="submit">Delete</button>
            </form>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</body>
  </html>
  
