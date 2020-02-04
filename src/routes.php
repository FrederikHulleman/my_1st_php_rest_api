<?php

use Slim\Http\Request;
use Slim\Http\Response;
use App\Exception\ApiException;

// Routes
$app->get('/', function (Request $request, Response $response, array $args) {
    $endpoints =  [
        'all courses' => $this->api['api_url'] . '/courses',
        'single course' => $this->api['api_url'] . '/courses/{course_id}',
        'reviews by course' => $this->api['api_url'] . '/courses/{course_id}/reviews',
        'single review' => $this->api['api_url'] . '/courses/{course_id}/reviews/{review_id}',
        'help' => $this->api['base_url']. '/',
    ];
    $result = [
      'endpoints' => $endpoints,
      'version' => $this->api['version'],
      'timestamp' => time(),
    ];
    return $response->withJson($result,200,JSON_PRETTY_PRINT);
});

$app->group('/api/v1/courses',function() use($app) {
    $app->get('', function (Request $request, Response $response, array $args) {
        $result = $this->course->orderBy('id','asc')->get();
        if(empty($result)) {
            throw new ApiException(ApiException::COURSE_NOT_FOUND,404);
        }
        return $response->withJson($result,200,JSON_PRETTY_PRINT);
    });
    $app->get('/{course_id}', function (Request $request, Response $response, array $args) {
        $result = $this->course->find($args['course_id']);
        if(empty($result)) {
            throw new ApiException(ApiException::COURSE_NOT_FOUND,404);
        }
        return $response->withJson($result,200,JSON_PRETTY_PRINT);
    });
    $app->post('', function (Request $request, Response $response, array $args) {
        $data = $request->getParsedBody();
        if(empty($data['title']) || empty($data['url'])) {
            throw new ApiException(ApiException::COURSE_INFO_REQUIRED);
        }
        $result = $this->course->create($data);
        if(empty($result)) {
            throw new ApiException(ApiException::COURSE_CREATION_FAILED);
        }
        return $response->withJson($result,201,JSON_PRETTY_PRINT);
    });
    $app->put('/{course_id}', function (Request $request, Response $response, array $args) {
        $data = $request->getParsedBody();
        if(empty($args['course_id']) || empty($data['title']) || empty($data['url'])) {
            throw new ApiException(ApiException::COURSE_INFO_REQUIRED);
        }
        $result_find = $this->course->find($args['course_id']);
        if(empty($result_find)) {
            throw new ApiException(ApiException::COURSE_NOT_FOUND);
        }
        $row_count_update = $result_find->update($data);
        if($row_count_update < 1) {
            throw new ApiException(ApiException::COURSE_UPDATE_FAILED);
        }
        $result = $this->course->find($args['course_id']);
        return $response->withJson($result,201,JSON_PRETTY_PRINT);
    });
    $app->delete('/{course_id}', function (Request $request, Response $response, array $args) {
        if(empty($args['course_id'])) {
            throw new ApiException(ApiException::COURSE_INFO_REQUIRED);
        }
        $result_find = $this->course->find($args['course_id']);
        if(empty($result_find)) {
            throw new ApiException(ApiException::COURSE_NOT_FOUND);
        }
        $row_count_delete = $result_find->delete();
        if($row_count_delete < 1) {
            throw new ApiException(ApiException::COURSE_DELETE_FAILED);
        }
        $result = ["message" => "The course was deleted"];
        return $response->withJson($result,201,JSON_PRETTY_PRINT);

    });
    $app->group('/{course_id}/reviews', function() use ($app) {
        $app->get('', function (Request $request, Response $response, array $args) {
            try {
                $result = $this->review->where('course_id',$args['course_id'])->get();
                $this->logger->info("View course reviews ".$args['course_id']." | SUCCESSFUL");
            }
            catch(\Exception $e) {
                $this->logger->error("View course reviews ".$args['course_id']." | UNSUCCESSFUL | " . $e->getMessage());
            }
            return $response->withJson($result,200,JSON_PRETTY_PRINT);
        });
        $app->get('/{review_id}', function (Request $request, Response $response, array $args) {
            try {
                $result = $this->review->find($args['review_id']);
                $this->logger->info("View course ".$args['course_id']." review ".$args['review_id']." | SUCCESSFUL");
            }
            catch(\Exception $e) {
                $this->logger->error("View course ".$args['course_id']." review ".$args['review_id']." | UNSUCCESSFUL | " . $e->getMessage());
            }
            return $response->withJson($result,200,JSON_PRETTY_PRINT);
        });
        $app->post('', function (Request $request, Response $response, array $args) {
            $result = $this->course->find($args['course_id'])->reviews()->create($request->getParsedBody());

            return $response->withJson($result,201,JSON_PRETTY_PRINT);

        });
        $app->put('/{review_id}', function (Request $request, Response $response, array $args) {
            $result = $this->review->updateOrCreate(['id'=>$args['review_id']],$request->getParsedBody());

            return $response->withJson($result,201,JSON_PRETTY_PRINT);

        });
        $app->delete('/{review_id}', function (Request $request, Response $response, array $args) {
            $result = $this->review->find($args['review_id'])->delete();

            return $response->withJson($result,200,JSON_PRETTY_PRINT);

        });
    });
});


