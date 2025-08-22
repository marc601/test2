<?php

use PHPUnit\Framework\TestCase;
use App\Controllers\Api\TaskApiController;
use App\Core\Database;
use App\Models\Task;
use App\Core\JsonResponse;

class MockJsonResponse
{
    public static $lastResponse = null;
    public static $lastStatusCode = null;

    public static function ok($data)
    {
        self::$lastResponse = $data;
        self::$lastStatusCode = 200;
    }

    public static function created($data)
    {
        self::$lastResponse = $data;
        self::$lastStatusCode = 201;
    }

    public static function notFound($message)
    {
        self::$lastResponse = ['message' => $message];
        self::$lastStatusCode = 404;
    }

    public static function unprocessable($errors)
    {
        self::$lastResponse = $errors;
        self::$lastStatusCode = 422;
    }

    public static function serverError($message)
    {
        self::$lastResponse = ['message' => $message];
        self::$lastStatusCode = 500;
    }

    public static function send($statusCode, $data)
    {
        self::$lastResponse = $data;
        self::$lastStatusCode = $statusCode;
    }

    public static function reset()
    {
        self::$lastResponse = null;
        self::$lastStatusCode = null;
    }
}

// This part is problematic and will be removed after refactoring TaskApiController
// if (!function_exists('App\\Core\\JsonResponse')) {
//     function JsonResponse() {
//         return new MockJsonResponse();
//     }
// }

class TaskApiControllerTest extends TestCase
{
    protected $taskModelMock;
    protected $databaseMock;
    protected $controller;
    protected $jsonResponseMock; // New mock for JsonResponse

    protected function setUp(): void
    {
        // Reset the mock JsonResponse before each test
        MockJsonResponse::reset();

        // Create a mock for the Database class
        $this->databaseMock = $this->createMock(Database::class);

        // Create a mock for the Task model
        $this->taskModelMock = $this->getMockBuilder(Task::class)
            ->setConstructorArgs([$this->databaseMock])
            ->onlyMethods(['findbyField', 'findById', 'saveRecord', 'validate', 'delete', 'getmetadata'])
            ->getMock();

        // Create a mock for JsonResponse
        $this->jsonResponseMock = $this->getMockBuilder(JsonResponse::class)
                                        ->disableOriginalConstructor()
                                        ->onlyMethods(['ok', 'created', 'notFound', 'unprocessable', 'serverError', 'send'])
                                        ->getMock();

        // Create an instance of the controller with mocked dependencies
        $this->controller = $this->getMockBuilder(TaskApiController::class)
            ->setConstructorArgs([$this->taskModelMock, $this->jsonResponseMock]) // Pass both mocks
            ->onlyMethods(['authenticate'])
            ->getMock();
    }

    // Helper to simulate php://input
    protected function setPhpInput(array $data)
    {
        $json = json_encode($data);
        file_put_contents('php://input', $json);
    }

    public function testIndexReturnsTasksForAuthenticatedUser()
    {
        $userId = 1;
        $expectedTasks = [
            (object)['id' => 1, 'user_id' => $userId, 'title' => 'Task 1', 'status' => 1, 'due_date' => '2025-01-01'],
            (object)['id' => 2, 'user_id' => $userId, 'title' => 'Task 2', 'status' => 0, 'due_date' => '2025-01-02']
        ];

        $this->controller->expects($this->once())
            ->method('authenticate')
            ->willReturn($userId);

        $this->taskModelMock->expects($this->once())
            ->method('findbyField')
            ->with('user_id', $userId)
            ->willReturn($expectedTasks);

        $this->jsonResponseMock->expects($this->once())
                               ->method('ok')
                               ->with($expectedTasks);

        $this->controller->index();
    }

    public function testShowReturnsTaskIfAuthorized()
    {
        $userId = 1;
        $taskId = 10;
        $expectedTaskData = ['id' => $taskId, 'user_id' => $userId, 'title' => 'Task 10', 'status' => 1, 'due_date' => '2025-03-15'];

        // Create a mock Task object that has the toArray method
        $taskMock = $this->getMockBuilder(Task::class)
                         ->disableOriginalConstructor()
                         ->onlyMethods(['toArray'])
                         ->getMock();
        // Set properties on the mock task object
        foreach ($expectedTaskData as $key => $value) {
            $taskMock->$key = $value;
        }

        $taskMock->expects($this->once())
                 ->method('toArray')
                 ->willReturn($expectedTaskData);

        $this->controller->expects($this->once())
                         ->method('authenticate')
                         ->willReturn($userId);

        $this->taskModelMock->expects($this->once())
                             ->method('findById')
                             ->with($taskId)
                             ->willReturn($taskMock);

        $this->jsonResponseMock->expects($this->once())
                               ->method('ok')
                               ->with($expectedTaskData);

        $this->controller->show($taskId);
    }

    public function testShowReturnsNotFoundIfTaskDoesNotExist()
    {
        $userId = 1;
        $taskId = 99;

        $this->controller->expects($this->once())
                         ->method('authenticate')
                         ->willReturn($userId);

        $this->taskModelMock->expects($this->once())
                             ->method('findById')
                             ->with($taskId)
                             ->willReturn(null);

        $this->jsonResponseMock->expects($this->once())
                               ->method('notFound')
                               ->with('Task not found or unauthorized');

        $this->controller->show($taskId);
    }

    public function testShowReturnsNotFoundIfTaskDoesNotBelongToUser()
    {
        $userId = 1;
        $otherUserId = 2;
        $taskId = 10;
        $task = (object)['id' => $taskId, 'user_id' => $otherUserId, 'title' => 'Task 10', 'status' => 1];

        $this->controller->expects($this->once())
                         ->method('authenticate')
                         ->willReturn($userId);

        $this->taskModelMock->expects($this->once())
                             ->method('findById')
                             ->with($taskId)
                             ->willReturn($task);

        $this->jsonResponseMock->expects($this->once())
                               ->method('notFound')
                               ->with('Task not found or unauthorized');

        $this->controller->show($taskId);
    }

    public function testStoreCreatesTaskSuccessfully()
    {
        $userId = 1;
        $taskData = [
            'title' => 'New Task',
            'description' => 'Description for new task',
            'status' => 0,
            'due_date' => '2025-12-25'
        ];

        $this->controller->expects($this->once())
                         ->method('authenticate')
                         ->willReturn($userId);

        // Mock the Task object that will be created inside the controller's store method
        $taskMock = $this->getMockBuilder(Task::class)
                         ->disableOriginalConstructor()
                         ->onlyMethods(['validate', 'saveRecord', 'toArray'])
                         ->getMock();

        // Set expectations for the task object's methods
        $taskMock->expects($this->once())
                 ->method('validate')
                 ->willReturn([]); // No validation errors

        $taskMock->expects($this->once())
                 ->method('saveRecord')
                 ->willReturn(true);

        $taskMock->expects($this->once())
                 ->method('toArray')
                 ->willReturn(array_merge($taskData, ['id' => 1, 'user_id' => $userId]));

        // This is the problematic part: mocking the `new Task()` call inside the controller.
        // PHPUnit doesn't directly support mocking `new` operator. This test will likely fail
        // unless the controller is refactored to use a factory or similar for Task creation.
        // For now, we'll leave this as a conceptual test.

        $this->setPhpInput($taskData);

        $this->jsonResponseMock->expects($this->once())
                               ->method('created')
                               ->with(array_merge($taskData, ['id' => 1, 'user_id' => $userId]));

        $this->controller->store();
    }

    public function testStoreReturnsUnprocessableIfValidationFails()
    {
        $userId = 1;
        $taskData = [
            'title' => '',
            'description' => 'Description for new task',
            'status' => 0
        ];
        $validationErrors = ['title' => 'Title is required'];

        $this->controller->expects($this->once())
                         ->method('authenticate')
                         ->willReturn($userId);

        $taskMock = $this->getMockBuilder(Task::class)
                         ->disableOriginalConstructor()
                         ->onlyMethods(['validate'])
                         ->getMock();

        $taskMock->expects($this->once())
                 ->method('validate')
                 ->willReturn($validationErrors);

        // Problematic part: mocking `new Task()`

        $this->setPhpInput($taskData);

        $this->jsonResponseMock->expects($this->once())
                               ->method('unprocessable')
                               ->with($validationErrors);

        $this->controller->store();
    }

    public function testStoreReturnsBadRequestForInvalidJson()
    {
        $this->controller->expects($this->once())
                         ->method('authenticate')
                         ->willReturn(1);

        // Simulate invalid JSON input
        file_put_contents('php://input', 'invalid json');

        $this->jsonResponseMock->expects($this->once())
                               ->method('send')
                               ->with(400, ['message' => 'Invalid JSON or empty request body']);

        $this->controller->store();
    }

    public function testUpdateUpdatesTaskSuccessfully()
    {
        $userId = 1;
        $taskId = 1;
        $existingTaskData = ['id' => $taskId, 'user_id' => $userId, 'title' => 'Old Title', 'description' => 'Old Desc', 'status' => 0, 'due_date' => '2025-01-01'];
        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated Desc',
            'status' => 1,
            'due_date' => '2025-02-02'
        ];

        // Create a mock Task object that has the toArray method
        $taskMock = $this->getMockBuilder(Task::class)
                         ->disableOriginalConstructor()
                         ->onlyMethods(['validate', 'saveRecord', 'toArray'])
                         ->getMock();
        // Set properties on the mock task object
        foreach ($existingTaskData as $key => $value) {
            $taskMock->$key = $value;
        }

        $taskMock->expects($this->once())
                 ->method('validate')
                 ->willReturn([]);

        $taskMock->expects($this->once())
                 ->method('saveRecord')
                 ->willReturn(true);

        $taskMock->expects($this->once())
                 ->method('toArray')
                 ->willReturn(array_merge($existingTaskData, $updateData));

        $this->controller->expects($this->once())
                         ->method('authenticate')
                         ->willReturn($userId);

        $this->taskModelMock->expects($this->once())
                             ->method('findById')
                             ->with($taskId)
                             ->willReturn($taskMock);

        $this->setPhpInput($updateData);

        $this->jsonResponseMock->expects($this->once())
                               ->method('ok')
                               ->with(array_merge($existingTaskData, $updateData));

        $this->controller->update($taskId);
    }

    public function testUpdateReturnsNotFoundIfTaskDoesNotExist()
    {
        $userId = 1;
        $taskId = 99;

        $this->controller->expects($this->once())
                         ->method('authenticate')
                         ->willReturn($userId);

        $this->taskModelMock->expects($this->once())
                             ->method('findById')
                             ->with($taskId)
                             ->willReturn(null);

        $this->setPhpInput(['title' => 'Any Title']);

        $this->jsonResponseMock->expects($this->once())
                               ->method('notFound')
                               ->with('Task not found or unauthorized');

        $this->controller->update($taskId);
    }

    public function testUpdateReturnsNotFoundIfTaskDoesNotBelongToUser()
    {
        $userId = 1;
        $otherUserId = 2;
        $taskId = 10;
        $task = (object)['id' => $taskId, 'user_id' => $otherUserId, 'title' => 'Task 10', 'status' => 1];

        $this->controller->expects($this->once())
                         ->method('authenticate')
                         ->willReturn($userId);

        $this->taskModelMock->expects($this->once())
                             ->method('findById')
                             ->with($taskId)
                             ->willReturn($task);

        $this->setPhpInput(['title' => 'Any Title']);

        $this->jsonResponseMock->expects($this->once())
                               ->method('notFound')
                               ->with('Task not found or unauthorized');

        $this->controller->update($taskId);
    }

    public function testDeleteDeletesTaskSuccessfully()
    {
        $userId = 1;
        $taskId = 1;
        $task = (object)['id' => $taskId, 'user_id' => $userId, 'title' => 'Task to delete', 'status' => 0];

        $this->controller->expects($this->once())
                         ->method('authenticate')
                         ->willReturn($userId);

        $this->taskModelMock->expects($this->once())
                             ->method('findById')
                             ->with($taskId)
                             ->willReturn($task);

        $this->taskModelMock->expects($this->once())
                             ->method('delete')
                             ->with($this->taskModelMock->getmetadata(), $taskId)
                             ->willReturn(true);

        $this->jsonResponseMock->expects($this->once())
                               ->method('ok')
                               ->with(['message' => 'Task deleted']);

        $this->controller->delete($taskId);
    }

    public function testDeleteReturnsNotFoundIfTaskDoesNotExist()
    {
        $userId = 1;
        $taskId = 99;

        $this->controller->expects($this->once())
                         ->method('authenticate')
                         ->willReturn($userId);

        $this->taskModelMock->expects($this->once())
                             ->method('findById')
                             ->with($taskId)
                             ->willReturn(null);

        $this->jsonResponseMock->expects($this->once())
                               ->method('notFound')
                               ->with('Task not found or unauthorized');

        $this->controller->delete($taskId);
    }

    public function testDeleteReturnsNotFoundIfTaskDoesNotBelongToUser()
    {
        $userId = 1;
        $otherUserId = 2;
        $taskId = 10;
        $task = (object)['id' => $taskId, 'user_id' => $otherUserId, 'title' => 'Task 10', 'status' => 1];

        $this->controller->expects($this->once())
                         ->method('authenticate')
                         ->willReturn($userId);

        $this->taskModelMock->expects($this->once())
                             ->method('findById')
                             ->with($taskId)
                             ->willReturn($task);

        $this->jsonResponseMock->expects($this->once())
                               ->method('notFound')
                               ->with('Task not found or unauthorized');

        $this->controller->delete($taskId);
    }
}
