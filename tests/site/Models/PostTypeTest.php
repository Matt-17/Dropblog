<?php

namespace Dropblog\Tests\Models;

use PHPUnit\Framework\TestCase;
use Dropblog\Models\PostType;
use InvalidArgumentException;

class PostTypeTest extends TestCase
{
    private PostType $postType;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->postType = new PostType();
        TestDatabase::resetTestDatabase();
    }
    
    protected function tearDown(): void
    {
        PostType::clearCache();
        parent::tearDown();
    }

    // ===== GET TESTS =====
    
    public function testGetAllActiveReturnsActivePostTypesOrderedBySortOrder(): void
    {
        $result = $this->postType->getAllActive();
        
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        
        // Check ordering by sort_order
        $this->assertEquals('note', $result[0]['slug']);
        $this->assertEquals('link', $result[1]['slug']);
        $this->assertEquals('photo', $result[2]['slug']);
        
        // Check that all have required fields
        foreach ($result as $type) {
            $this->assertArrayHasKey('id', $type);
            $this->assertArrayHasKey('slug', $type);
            $this->assertArrayHasKey('name', $type);
            $this->assertArrayHasKey('icon_filename', $type);
            $this->assertArrayHasKey('sort_order', $type);
        }
    }
    
    public function testGetAllActiveUsesCaching(): void
    {
        // First call should query database
        $result1 = $this->postType->getAllActive();
        
        // Second call should use cache (we can't directly test this but can verify results are consistent)
        $result2 = $this->postType->getAllActive();
        
        $this->assertEquals($result1, $result2);
    }
    
    public function testGetAllReturnsAllPostTypesIncludingInactive(): void
    {
        // Create an inactive post type
        $this->postType->create([
            'slug' => 'inactive-test',
            'name' => 'Inactive Test',
            'icon_filename' => 'test.svg',
            'is_active' => 0
        ]);
        
        $result = $this->postType->getAll();
        
        $this->assertGreaterThan(3, count($result));
        
        // Check that we have both active and inactive types
        $activeCount = count(array_filter($result, fn($type) => $type['is_active'] == 1));
        $inactiveCount = count(array_filter($result, fn($type) => $type['is_active'] == 0));
        
        $this->assertGreaterThanOrEqual(3, $activeCount);
        $this->assertGreaterThanOrEqual(1, $inactiveCount);
    }
    
    public function testGetBySlugReturnsCorrectPostType(): void
    {
        $result = $this->postType->getBySlug('note');
        
        $this->assertIsArray($result);
        $this->assertEquals('note', $result['slug']);
        $this->assertEquals('Note', $result['name']);
        $this->assertEquals('note.svg', $result['icon_filename']);
    }
    
    public function testGetBySlugReturnsFalseForNonexistentSlug(): void
    {
        $result = $this->postType->getBySlug('nonexistent');
        
        $this->assertFalse($result);
    }
    
    public function testGetBySlugReturnsFalseForInactivePostType(): void
    {
        // Create inactive post type
        $id = $this->postType->create([
            'slug' => 'inactive-test',
            'name' => 'Inactive Test',
            'icon_filename' => 'test.svg',
            'is_active' => 0
        ]);
        
        $result = $this->postType->getBySlug('inactive-test');
        
        $this->assertFalse($result);
    }
    
    public function testGetByIdReturnsCorrectPostType(): void
    {
        $result = $this->postType->getById(1);
        
        $this->assertIsArray($result);
        $this->assertEquals(1, $result['id']);
        $this->assertEquals('note', $result['slug']);
    }
    
    public function testGetByIdReturnsFalseForNonexistentId(): void
    {
        $result = $this->postType->getById(999);
        
        $this->assertFalse($result);
    }

    // ===== CREATE TESTS =====
    
    public function testCreateWithValidDataReturnsId(): void
    {
        $data = [
            'slug' => 'test-type',
            'name' => 'Test Type',
            'description' => 'A test post type',
            'icon_filename' => 'test.svg',
            'sort_order' => 10
        ];
        
        $id = $this->postType->create($data);
        
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
        
        // Verify the post type was created
        $created = $this->postType->getById($id);
        $this->assertEquals('test-type', $created['slug']);
        $this->assertEquals('Test Type', $created['name']);
        $this->assertEquals('A test post type', $created['description']);
        $this->assertEquals('test.svg', $created['icon_filename']);
        $this->assertEquals(10, $created['sort_order']);
        $this->assertEquals(1, $created['is_active']); // Default value
    }
    
    public function testCreateWithMinimalDataUsesDefaults(): void
    {
        $data = [
            'slug' => 'minimal',
            'name' => 'Minimal',
            'icon_filename' => 'minimal.svg'
        ];
        
        $id = $this->postType->create($data);
        $created = $this->postType->getById($id);
        
        $this->assertNull($created['description']);
        $this->assertEquals(1, $created['is_active']);
        $this->assertEquals(0, $created['sort_order']);
    }
    
    public function testCreateThrowsExceptionForMissingSlug(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Slug, name, and icon_filename are required');
        
        $this->postType->create([
            'name' => 'Test',
            'icon_filename' => 'test.svg'
        ]);
    }
    
    public function testCreateThrowsExceptionForMissingName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Slug, name, and icon_filename are required');
        
        $this->postType->create([
            'slug' => 'test',
            'icon_filename' => 'test.svg'
        ]);
    }
    
    public function testCreateThrowsExceptionForMissingIconFilename(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Slug, name, and icon_filename are required');
        
        $this->postType->create([
            'slug' => 'test',
            'name' => 'Test'
        ]);
    }
    
    public function testCreateThrowsExceptionForDuplicateSlug(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A post type with this slug already exists');
        
        $this->postType->create([
            'slug' => 'note', // Already exists
            'name' => 'Duplicate Note',
            'icon_filename' => 'dup.svg'
        ]);
    }
    
    public function testCreateClearsCache(): void
    {
        // Load cache
        $this->postType->getAllActive();
        
        // Create new post type
        $this->postType->create([
            'slug' => 'new-type',
            'name' => 'New Type',
            'icon_filename' => 'new.svg'
        ]);
        
        // Cache should be cleared and new type should appear
        $result = $this->postType->getAllActive();
        $slugs = array_column($result, 'slug');
        
        $this->assertContains('new-type', $slugs);
    }

    // ===== UPDATE TESTS =====
    
    public function testUpdateWithValidDataReturnsTrue(): void
    {
        $result = $this->postType->update(1, [
            'name' => 'Updated Note',
            'description' => 'Updated description',
            'sort_order' => 5
        ]);
        
        $this->assertTrue($result);
        
        // Verify changes
        $updated = $this->postType->getById(1);
        $this->assertEquals('Updated Note', $updated['name']);
        $this->assertEquals('Updated description', $updated['description']);
        $this->assertEquals(5, $updated['sort_order']);
        $this->assertEquals('note', $updated['slug']); // Unchanged
    }
    
    public function testUpdateWithEmptyDataReturnsTrue(): void
    {
        $result = $this->postType->update(1, []);
        
        $this->assertTrue($result); // Nothing to update is still success
    }
    
    public function testUpdateThrowsExceptionForNonexistentId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Post type not found');
        
        $this->postType->update(999, ['name' => 'Test']);
    }
    
    public function testUpdateThrowsExceptionForDuplicateSlug(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A post type with this slug already exists');
        
        $this->postType->update(1, ['slug' => 'link']); // Link already exists
    }
    
    public function testUpdateAllowsSameSlugForSameRecord(): void
    {
        $result = $this->postType->update(1, ['slug' => 'note']); // Same slug
        
        $this->assertTrue($result);
    }
    
    public function testUpdateClearsCache(): void
    {
        // Load cache
        $this->postType->getAllActive();
        
        // Update post type
        $this->postType->update(1, ['name' => 'Updated Note']);
        
        // Cache should be cleared and change should appear
        $result = $this->postType->getAllActive();
        $noteType = array_filter($result, fn($type) => $type['slug'] === 'note')[0];
        
        $this->assertEquals('Updated Note', $noteType['name']);
    }

    // ===== DELETE TESTS =====
    
    public function testDeleteWithNoPostsReturnsTrue(): void
    {
        // Create a post type with no posts
        $id = $this->postType->create([
            'slug' => 'unused',
            'name' => 'Unused',
            'icon_filename' => 'unused.svg'
        ]);
        
        $result = $this->postType->delete($id);
        
        $this->assertTrue($result);
        
        // Verify it's deleted
        $deleted = $this->postType->getById($id);
        $this->assertFalse($deleted);
    }
    
    public function testDeleteThrowsExceptionForNonexistentId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Post type not found');
        
        $this->postType->delete(999);
    }

    // ===== UTILITY METHOD TESTS =====
    
    public function testGetIconPathWithValidPostType(): void
    {
        $postTypeData = ['icon_filename' => 'note.svg'];
        $result = PostType::getIconPath($postTypeData);
        
        $this->assertEquals('/post-types/note.svg', $result);
    }
    
    public function testGetIconPathWithEmptyFilename(): void
    {
        $postTypeData = ['icon_filename' => ''];
        $result = PostType::getIconPath($postTypeData);
        
        $this->assertEquals('/post-types/', $result);
    }
    
    public function testGetUsageStatsReturnsCorrectStructure(): void
    {
        $result = $this->postType->getUsageStats();
        
        $this->assertIsArray($result);
        
        foreach ($result as $stat) {
            $this->assertArrayHasKey('id', $stat);
            $this->assertArrayHasKey('slug', $stat);
            $this->assertArrayHasKey('name', $stat);
            $this->assertArrayHasKey('icon_filename', $stat);
            $this->assertArrayHasKey('post_count', $stat);
            $this->assertIsInt($stat['post_count']);
        }
    }
    
    public function testClearCacheMethodWorks(): void
    {
        // This is mainly to ensure the method exists and doesn't throw
        PostType::clearCache();
        $this->assertTrue(true);
    }
    
    public function testIsValidSlugWithValidSlugs(): void
    {
        $this->assertTrue(PostType::isValidSlug('note'));
        $this->assertTrue(PostType::isValidSlug('my-post-type'));
        $this->assertTrue(PostType::isValidSlug('post123'));
        $this->assertTrue(PostType::isValidSlug('a'));
    }
    
    public function testIsValidSlugWithInvalidSlugs(): void
    {
        $this->assertFalse(PostType::isValidSlug(''));
        $this->assertFalse(PostType::isValidSlug('Post Type')); // Spaces
        $this->assertFalse(PostType::isValidSlug('post_type')); // Underscore
        $this->assertFalse(PostType::isValidSlug('post.type')); // Dot
        $this->assertFalse(PostType::isValidSlug('POST')); // Uppercase
        $this->assertFalse(PostType::isValidSlug(str_repeat('a', 51))); // Too long
    }
} 