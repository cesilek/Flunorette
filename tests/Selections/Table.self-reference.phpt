<?php

/**
 * Test: Nette\Database\Table: DiscoveredReflection with self-reference.
 *
 * @author     Jan Skrasek
 * @dataProvider? databases.ini
*/

require __DIR__ . '/connect.inc.php'; // create $connection

Flunorette\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");
$connection->setDatabaseReflection($ref = new \Flunorette\DiscoveredReflection($connection));

global $driverName;
switch ($driverName) {
	case 'pgsql':
		$connection->query('ALTER TABLE "book" ADD COLUMN "next_volume" int NULL;');
		$connection->query('ALTER TABLE "book" ADD CONSTRAINT "book_volume" FOREIGN KEY ("next_volume") REFERENCES "book" ("id") ON DELETE RESTRICT ON UPDATE RESTRICT;');
		$connection->query('UPDATE "book" SET "next_volume" = 3 WHERE "id" IN (2,4)');
		break;
	case 'mysql':
		$connection->query('ALTER TABLE `book` ADD COLUMN `next_volume` int NULL AFTER `title`;');
		$connection->query('ALTER TABLE `book` ADD CONSTRAINT `book_volume` FOREIGN KEY (`next_volume`) REFERENCES `book` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;');
		$connection->query('UPDATE `book` SET `next_volume` = 3 WHERE `id` IN (2,4)');
		break;
	default:
		Assert::fail("Unsupported driver $driverName");
}

$ref->reload();

$book = $connection->table('book')->get(4);
Assert::same('Nette', $book->volume->title);
Assert::same('Nette', $book->ref('book', 'next_volume')->title);


$book = $connection->table('book')->get(3);
Assert::same(2, $book->related('book.next_volume')->count('*'));
Assert::same(2, $book->related('book', 'next_volume')->count('*'));
