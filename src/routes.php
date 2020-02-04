<?php

use App\Model\Course;
use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->delete('/course/delete/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];
    $course = $this->course->findOrFail($id)->delete();

    return $response->withJson($course,200,JSON_PRETTY_PRINT);

});

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
        try {
            $result = $this->course->orderBy('id','asc')->get();
            $this->logger->info("View courses | SUCCESSFUL");
        }
        catch(\Exception $e){
            $this->logger->error("View courses | UNSUCCESSFUL | " . $e->getMessage());
        }
        return $response->withJson($result,200,JSON_PRETTY_PRINT);
    });
    $app->get('/{course_id}', function (Request $request, Response $response, array $args) {
        try {
            $result = $this->course->findOrFail($args['course_id']);
            $this->logger->info("View course ".$args['course_id']." | SUCCESSFUL");
        }
        catch(\Exception $e) {
            $this->logger->error("View course ".$args['course_id']." | UNSUCCESSFUL | " . $e->getMessage());
        }
        return $response->withJson($result,200,JSON_PRETTY_PRINT);
    });
    $app->post('', function (Request $request, Response $response, array $args) {
        $result = $this->course->create($request->getParsedBody());

        return $response->withJson($result,201,JSON_PRETTY_PRINT);

    });
    $app->put('/{course_id}', function (Request $request, Response $response, array $args) {
        $result = $this->course->updateOrCreate(['id'=>$args['course_id']],$request->getParsedBody());

        return $response->withJson($result,201,JSON_PRETTY_PRINT);

    });
    $app->delete('/{course_id}', function (Request $request, Response $response, array $args) {
        $result = $this->course->findOrFail($args['course_id'])->delete();

        return $response->withJson($result,200,JSON_PRETTY_PRINT);

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
                $result = $this->review->findOrFail($args['review_id']);
                $this->logger->info("View course ".$args['course_id']." review ".$args['review_id']." | SUCCESSFUL");
            }
            catch(\Exception $e) {
                $this->logger->error("View course ".$args['course_id']." review ".$args['review_id']." | UNSUCCESSFUL | " . $e->getMessage());
            }
            return $response->withJson($result,200,JSON_PRETTY_PRINT);
        });
        $app->post('', function (Request $request, Response $response, array $args) {
            $result = $this->course->findOrFail($args['course_id'])->reviews()->create($request->getParsedBody());

            return $response->withJson($result,201,JSON_PRETTY_PRINT);

        });
        $app->put('/{review_id}', function (Request $request, Response $response, array $args) {
            $result = $this->review->updateOrCreate(['id'=>$args['review_id']],$request->getParsedBody());

            return $response->withJson($result,201,JSON_PRETTY_PRINT);

        });
        $app->delete('/{review_id}', function (Request $request, Response $response, array $args) {
            $result = $this->review->findOrFail($args['review_id'])->delete();

            return $response->withJson($result,200,JSON_PRETTY_PRINT);

        });
    });
});


