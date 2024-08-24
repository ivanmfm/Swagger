<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Validator;


/**
 * @OA\Tag(
 *     name="Blogs",
 *     description="Endpoints for managing blog posts"
 * ),
 */
class BlogController extends Controller
{
 /**
 * @OA\Get(
 *      path="/api/blog",
 *      operationId="index",
 *      tags={"Blogs"},
 *      summary="Get all blog posts",
 *      description="Retrieves all blog posts.",
 *      @OA\Response(
 *          response=200,
 *          description="Blog posts retrieved successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="status", type="string", example="success"),
 *              @OA\Property(property="message", type="string", example="Blog posts are retrieved successfully or No blog posts found!"),
 *              @OA\Property(property="data", type="array",
 *                  @OA\Items(
 *                      @OA\Property(property="id", type="integer", example=1),
 *                      @OA\Property(property="title", type="string", example="Blog Post Title"),
 *                      @OA\Property(property="content", type="string", example="Blog Post Content"),
 *                      @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-09T12:34:56Z"),
 *                      @OA\Property(property="updated_at", type="string", format="date-time", example="2024-03-09T12:34:56Z"),
 *                  ),
 *              ),
 *          ),
 *      ),
 * 
 *     security={
 *         {"bearerAuth": {}}
 *     }
 * )
 */
    public function index()
    {
        $contents = Blog::latest()->get();

        if (is_null($contents->first())) {
            return response()->json([
                'status' => 'failed',
                'message' => 'No product found!',
            ], 200);
        }

        $response = [
            'status' => 'success',
            'message' => 'Blog post are retrieved successfully.',
            'data' => $contents,
        ];

        return response()->json($contents, 200);
    }

/**
 * @OA\Post(
 *      path="/api/blog",
 *      operationId="store",
 *      tags={"Blogs"},
 *      summary="Create a new blog post",
 *      description="Creates a new blog post with the provided title and content.",
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(
 *              mediaType="application/json",
 *              @OA\Schema(
 *                  @OA\Property(property="title", type="string", example="New Blog Post"),
 *                  @OA\Property(property="content", type="string", example="This is the content of the blog post."),
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Blog post added successfully",
 *          @OA\JsonContent(
 *              @OA\Property(property="status", type="string", example="success"),
 *              @OA\Property(property="message", type="string", example="Blog post is added successfully."),
 *              @OA\Property(property="data", type="object",
 *                  @OA\Property(property="id", type="integer", example=1),
 *                  @OA\Property(property="title", type="string", example="New Blog Post"),
 *                  @OA\Property(property="content", type="string", example="This is the content of the blog post."),
 *                  @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-09T12:34:56Z"),
 *                  @OA\Property(property="updated_at", type="string", format="date-time", example="2024-03-09T12:34:56Z"),
 *              ),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Validation Error",
 *          @OA\JsonContent(
 *              @OA\Property(property="status", type="string", example="failed"),
 *              @OA\Property(property="message", type="string", example="Validation Error!"),
 *              @OA\Property(property="data", type="object", example={"title": {"The title field is required."}}),
 *          ),
 *      ),
 * 
 *     security={
 *         {"bearerAuth": {}}
 *     }
 * )
 */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'title' => 'required|string|max:250',
            'content' => 'required|string'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Error!',
                'data' => $validate->errors(),
            ], 403);
        }

        $contents = Blog::create($request->all());

        $response = [
            'status' => 'success',
            'message' => 'Blog post is added successfully.',
            'data' => $contents,
        ];

        return response()->json($response, 200);
    }

 /**
 * @OA\Get(
 *      path="/api/blog/{id}",
 *      operationId="show",
 *      tags={"Blogs"},
 *      summary="Get a specific blog post",
 *      description="Retrieves a specific blog post by its ID.",
 *      @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID of the blog post",
 *          @OA\Schema(type="integer", format="int64")
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Blog post retrieval response",
 *          @OA\JsonContent(
 *              @OA\Property(property="status", type="string", example="success"),
 *              @OA\Property(property="message", type="string"),
 *              @OA\Property(property="data", type="object",
 *                  oneOf={
 *                      @OA\Schema(
 *                          @OA\Property(property="id", type="integer", example=1),
 *                          @OA\Property(property="title", type="string", example="Blog Post Title"),
 *                          @OA\Property(property="content", type="string", example="Blog Post Content"),
 *                          @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-09T12:34:56Z"),
 *                          @OA\Property(property="updated_at", type="string", format="date-time", example="2024-03-09T12:34:56Z"),
 *                      ),
 *                      @OA\Schema(
 *                          @OA\Property(property="status", type="string", example="failed"),
 *                          @OA\Property(property="message", type="string", example="Blog post is not found!"),
 *                      ),
 *                  },
 *              ),
 *          ),
 *      ),
 * 
 *     security={
 *         {"bearerAuth": {}}
 *     }
 * )
 */
    public function show($id)
    {
        $contents = Blog::find($id);

        if (is_null($contents)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Blog post is not found!',
            ], 200);
        }

        $response = [
            'status' => 'success',
            'message' => 'Blog post is retrieved successfully.',
            'data' => $contents,
        ];

        return response()->json($response, 200);
    }

   /**
 * @OA\Post(
 *      path="/api/blog/{id}",
 *      operationId="update",
 *      tags={"Blogs"},
 *      summary="Update a specific blog post",
 *      description="Updates a specific blog post by its ID.",
 *      @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID of the blog post",
 *          @OA\Schema(type="integer", format="int64")
 *      ),
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(
 *              mediaType="application/json",
 *              @OA\Schema(
 *                  @OA\Property(property="title", type="string", example="Updated Blog Post"),
 *                  @OA\Property(property="content", type="string", example="This is the updated content of the blog post."),
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Blog post update response",
 *          @OA\JsonContent(
 *              @OA\Property(property="status", type="string", example="success"),
 *              @OA\Property(property="message", type="string"),
 *              @OA\Property(property="data", type="object",
 *                  oneOf={
 *                      @OA\Schema(
 *                          @OA\Property(property="id", type="integer", example=1),
 *                          @OA\Property(property="title", type="string", example="Updated Blog Post"),
 *                          @OA\Property(property="content", type="string", example="This is the updated content of the blog post.")
 *                      ),
 *                      @OA\Schema(
 *                          @OA\Property(property="status", type="string", example="failed"),
 *                          @OA\Property(property="message", type="string", example="Blog post is not found!"),
 *                      ),
 *                  },
 *              ),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Validation Error",
 *          @OA\JsonContent(
 *              @OA\Property(property="status", type="string", example="failed"),
 *              @OA\Property(property="message", type="string", example="Validation Error!"),
 *              @OA\Property(property="data", type="object", example={"title": {"The title field is required."}}),
 *          ),
 *      ),
 * 
 *     security={
 *         {"bearerAuth": {}}
 *     }
 * )
 */
    public function update(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'title' => 'required|string|max:250',
            'content' => 'required|string'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Error!',
                'data' => $validate->errors(),
            ], 403);
        }

        $content = Blog::find($id);

        if (is_null($content)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Blog post is not found!',
            ], 200);
        }

        $content->update($request->all());

        $response = [
            'status' => 'success',
            'message' => 'Blog post is updated successfully.',
            'data' => $content,
        ];

        return response()->json($response, 200);
    }

  /**
 * @OA\Delete(
 *      path="/api/blog/{id}",
 *      operationId="destroy",
 *      tags={"Blogs"},
 *      summary="Delete a specific blog post",
 *      description="Deletes a specific blog post by its ID.",
 *      @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID of the blog post",
 *          @OA\Schema(type="integer", format="int64")
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Blog post deletion response",
 *          @OA\JsonContent(
 *              @OA\Property(property="status", type="string", example="success"),
 *              @OA\Property(property="message", type="string"),
 *              @OA\Property(property="data", type="object",
 *                  oneOf={
 *                      @OA\Schema(
 *                          @OA\Property(property="id", type="integer", example=1),
 *                          @OA\Property(property="title", type="string", example="Deleted Blog Post"),
 *                          @OA\Property(property="content", type="string", example="This is the content of the deleted blog post.")
 *                      ),
 *                      @OA\Schema(
 *                          @OA\Property(property="status", type="string", example="failed"),
 *                          @OA\Property(property="message", type="string", example="Blog post is not found!"),
 *                      ),
 *                  },
 *              ),
 *          ),
 *      ),
 * 
 *     security={
 *         {"bearerAuth": {}}
 *     }
 * )
 */
    public function destroy($id)
    {
        $content = Blog::find($id);

        if (is_null($content)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Blog post is not found!',
            ], 200);
        }

        Blog::destroy($id);
        return response()->json([
            'status' => 'success',
            'message' => 'Blog post is deleted successfully.'
        ], 200);
    }

/**
 * @OA\Get(
 *      path="/api/blog/search/{title}",
 *      operationId="search",
 *      tags={"Blogs"},
 *      summary="Search for blog posts by title",
 *      description="Retrieves blog posts based on the provided title search.",
 *      @OA\Parameter(
 *          name="title",
 *          in="path",
 *          required=true,
 *          description="Title to search for",
 *          @OA\Schema(type="string")
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Blog posts retrieval response",
 *          @OA\JsonContent(
 *              @OA\Property(property="status", type="string", example="success"),
 *              @OA\Property(property="message", type="string"),
 *              @OA\Property(property="data", type="array", description="Array of blog posts",
 *                  @OA\Items(
 *                      @OA\Property(property="id", type="integer", example=1),
 *                      @OA\Property(property="title", type="string", example="Blog Post Title"),
 *                      @OA\Property(property="content", type="string", example="Blog Post Content")
 *                  ),
 *              ),
 *              @OA\Property(property="no_posts_found", type="boolean", example=false),
 *          ),
 *      ),
 * 
 *     security={
 *         {"bearerAuth": {}}
 *     }
 * )
 */
    public function search($title)
    {
        $content = Blog::where('title', 'like', '%' . $title . '%')
            ->latest()->get();

        if (is_null($content->first())) {
            return response()->json([
                'status' => 'failed',
                'message' => 'No blog posts found!',
            ], 200);
        }

        $response = [
            'status' => 'success',
            'message' => 'Blog posts are retrieved successfully.',
            'data' => $content,
        ];

        return response()->json($response, 200);
    }
}
