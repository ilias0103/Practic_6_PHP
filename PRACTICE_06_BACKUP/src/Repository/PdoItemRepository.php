<?php
declare(strict_types=1);
namespace Course\Repository;

use Course\Domain\Item;
use Course\Domain\ItemData;

final class PdoItemRepository implements ItemRepository
{
    public function __construct(private \PDO $pdo) {}
    private function item(array $row): Item
    {
        $ownerId = $row['owner_id'] === null
            ? null
            : (int) $row['owner_id'];
    
        return new Item(
            (int) $row['id'],
            $row['title'],
            $row['category'],
            (int) $row['quantity'],
            (int) $row['price_minor'],
            $ownerId
        );
    }
    // Готовый образец: запрос без внешнего ввода, поэтому без параметров.
    public function all(): array
    {
        $sql = 'SELECT id, title, category, quantity, price_minor, owner_id FROM items ORDER BY id';
        return array_map($this->item(...), $this->pdo->query($sql)->fetchAll());
    }
    public function find(int $id): ?Item
{
    $sql = 'SELECT id, title, category, quantity, price_minor, owner_id
            FROM items
            WHERE id = :id';

    $statement = $this->pdo->prepare($sql);
    $statement->execute(['id' => $id]);

    $row = $statement->fetch();

    if ($row === false) {
        return null;
    }

    return $this->item($row);
}
public function matching(string $query): array
{
    $escaped = addcslashes($query, '%_\\');

    $sql = 'SELECT id, title, category, quantity, price_minor, owner_id
            FROM items
            WHERE title LIKE :query
            ORDER BY id';

    $statement = $this->pdo->prepare($sql);

    $statement->execute([
        'query' => '%' . $escaped . '%',
    ]);

    return array_map(
        $this->item(...),
        $statement->fetchAll()
    );
}
public function create(ItemData $data, int $ownerId): Item
{
    $sql = 'INSERT INTO items (title, category, quantity, price_minor, owner_id)
            VALUES (:title, :category, :quantity, :price_minor, :owner_id)';

    $statement = $this->pdo->prepare($sql);

    $statement->execute([
        'title' => $data->title,
        'category' => $data->category,
        'quantity' => $data->quantity,
        'price_minor' => $data->priceMinor,
        'owner_id' => $ownerId,
    ]);

    $id = (int) $this->pdo->lastInsertId();

    $item = $this->find($id);

    if ($item === null) {
        throw new \RuntimeException('Не удалось получить созданную запись');
    }

    return $item;
}
}
