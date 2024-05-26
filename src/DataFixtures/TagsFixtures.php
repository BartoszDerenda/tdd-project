<?php
/**
 * Tags fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Tags;
use Cocur\Slugify\Slugify;
use DateTimeImmutable;

/**
 * Class TagsFixtures.
 *
 * @psalm-suppress MissingConstructor
 */
class TagsFixtures extends AbstractBaseFixtures
{
    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        $slugify = new Slugify();

        $this->createMany(50, 'tags', function (int $i) {
            $slugify = new Slugify();
            $tags = new Tags();
            $tags->setTitle($this->faker->unique()->word);
            $tags->setSlug($slugify->slugify($tags->getTitle())); // Set the slug based on the title
            $tags->setCreatedAt(
                DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $tags->setUpdatedAt(
                DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );

            return $tags;
        });

        $this->manager->flush();
    }
}
