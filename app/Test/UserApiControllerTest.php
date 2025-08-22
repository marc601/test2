<?php

use PHPUnit\Framework\TestCase;
use App\Controllers\Api\UserApiController;
use App\Models\User;

class UserApiControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (in_array('php', stream_get_wrappers())) {
            stream_wrapper_unregister('php');
        }
        stream_wrapper_register('php', TestInputStream::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (in_array('php', stream_get_wrappers())) {
            stream_wrapper_unregister('php');
        }
        stream_wrapper_restore('php');
    }

    protected function setRawInput(string $content): void
    {
        TestInputStream::$input = $content;
    }

    public function testIndexReturnsJsonUsers()
    {
        $mockUserModel = $this->createMock(User::class);

        $mockUserModel->method('find')->willReturn([
            User::fromArray([
                'id' => 1,
                'name' => 'Marco',
                'email' => 'marco@example.com',
                'password' => 'hashed_password_example',
                'created_at' => '2023-01-01 00:00:00',
                'updated_at' => '2023-01-01 00:00:00'
            ]),
            User::fromArray([
                'id' => 2,
                'name' => 'Ana',
                'email' => 'ana@example.com',
                'password' => 'hashed_password_example',
                'created_at' => '2023-01-01 00:00:00',
                'updated_at' => '2023-01-01 00:00:00'
            ]),
        ]);

        $controller = $this->getMockBuilder(UserApiController::class)
            ->onlyMethods(['authenticate'])
            ->getMock();

        $controller->method('authenticate')->willReturn(2);

        ob_start();
        $controller->index($mockUserModel);
        $output = ob_get_clean();
        $this->assertJson($output);

        $data = json_decode($output, true);
        $this->assertCount(2, $data);
        $this->assertEquals(1, $data[0]['id']);
        $this->assertEquals('Marco', $data[0]['name']);
        $this->assertEquals('marco@example.com', $data[0]['email']);
        $this->assertEquals(2, $data[1]['id']);
        $this->assertEquals('Ana', $data[1]['name']);
        $this->assertEquals('ana@example.com', $data[1]['email']);
    }

    public function testShowReturnsUserWhenFound()
    {
        $mockUserModel = $this->createMock(User::class);

        $mockUserModel->method('findById')->willReturn(
            User::fromArray([
                'id' => 1,
                'name' => 'Marco',
                'email' => 'marco@example.com',
                'password' => 'hashed_password_example',
                'created_at' => '2023-01-01 00:00:00',
                'updated_at' => '2023-01-01 00:00:00'
            ])
        );

        $controller = $this->getMockBuilder(UserApiController::class)
            ->onlyMethods(['authenticate'])
            ->getMock();

        $controller->method('authenticate')->willReturn(2);
        ob_start();
        $controller->show(1, $mockUserModel);
        $output = ob_get_clean();
        $this->assertJson($output);
        $data = json_decode($output, true);
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('Marco', $data['name']);
        $this->assertEquals('marco@example.com', $data['email']);
        $this->assertArrayNotHasKey('password', $data);
    }

    public function testShowReturnsNotFoundWhenUserNotFound()
    {
        $mockUserModel = $this->createMock(User::class);

        // Mock find to return null when user is not found
        $mockUserModel->method('findById')->willReturn(null);

        $controller = $this->getMockBuilder(UserApiController::class)
            ->onlyMethods(['authenticate'])
            ->getMock();

        $controller->method('authenticate')->willReturn(2);

        ob_start();
        $controller->show(999, $mockUserModel);
        $output = ob_get_clean();
        $this->assertJson($output);

        $data = json_decode($output, true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('User not found', $data['message']);
    }

    public function testStoreCreatesUserSuccessfully()
    {
        $mockUser = $this->createMock(User::class);
        $mockUser->method('saveRecord')->willReturn(true);
        $mockUser->method('toArray')->willReturn([
            'id' => 1,
            'name' => 'New User',
            'email' => 'new@example.com',
            'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
            'updated_at' => (new DateTime())->format('Y-m-d H:i:s')
        ]); 

        $controller = $this->getMockBuilder(UserApiController::class)
            ->onlyMethods(['authenticate'])
            ->getMock();

        $controller->method('authenticate')->willReturn(1);

        // Simulate php://input
        $inputJson = json_encode([
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'new_password'
        ]);
        $this->setRawInput($inputJson); // Use the helper method

        ob_start();
        $controller->store($mockUser);
        $output = ob_get_clean();

        $this->assertJson($output);
        $data = json_decode($output, true);

        $this->assertEquals('New User', $data['name']);
        $this->assertEquals('new@example.com', $data['email']);
        $this->assertArrayNotHasKey('password', $data); // Password should not be returned in the response
        $this->assertArrayHasKey('id', $data); // Assuming ID is set after saveRecord
        $this->assertEquals(1, $data['id']); // Assert the ID we set on the mock
    }

    public function testStoreFailsToCreateUser()
    {
        $mockUser = $this->createMock(User::class);
        $mockUser->method('saveRecord')->willReturn(false);

        $controller = $this->getMockBuilder(UserApiController::class)
            ->onlyMethods(['authenticate'])
            ->getMock();

        $controller->method('authenticate')->willReturn(1);

        // Simulate php://input
        $inputJson = json_encode([
            'name' => 'Failing User',
            'email' => 'failing@example.com',
            'password' => 'fail_password'
        ]);
        $this->setRawInput($inputJson); // Use the helper method

        ob_start();
        $controller->store($mockUser);
        $output = ob_get_clean();

        $this->assertJson($output);
        $data = json_decode($output, true);

        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Failed to create user', $data['message']);
    }
}

// Custom stream wrapper for php://input simulation
class TestInputStream
{
    public static string $input = '';
    private int $position;

    public function stream_open(string $path, string $mode, int $options, ?string &$opened_path): bool
    {
        if ($path === 'php://input') {
            $this->position = 0;
            return true;
        }
        return false;
    }

    public function stream_read(int $count): string
    {
        $ret = substr(self::$input, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

    public function stream_tell(): int
    {
        return $this->position;
    }

    public function stream_eof(): bool
    {
        return $this->position >= strlen(self::$input);
    }

    public function stream_stat(): array
    {
        return [];
    }

    public function stream_seek(int $offset, int $whence = SEEK_SET): bool
    {
        switch ($whence) {
            case SEEK_SET:
                $this->position = $offset;
                break;
            case SEEK_CUR:
                $this->position += $offset;
                break;
            case SEEK_END:
                $this->position = strlen(self::$input) + $offset;
                break;
        }
        return true;
    }
}
