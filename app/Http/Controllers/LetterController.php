<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\LetterRepository;

class LetterController extends Controller
{
    protected $letterRepository;

    public function __construct(LetterRepository $letterRepository)
    {
        $this->letterRepository = $letterRepository;
    }

    public function getAllLetter()
    {
        $listLetters = $this->letterRepository->getAllLetter();
        return response()->json(['message' => 'Letter Fetched succesfully', 'data' => $listLetters]);
    }

    public function show($id)
    {
        $letter = $this->letterRepository->getById($id);
        if ($letter) {
            return response()->json(['data' => $letter]);
        }
        return response()->json(['message' => 'Letter not found'], 404);
    }

    public function store(Request $request)
    {
        $data = $request->only(['user_id', 'field', 'member', 'status']);
        $letter = $this->letterRepository->create($data);
        return response()->json(['data' => $letter], 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->only(['user_id', 'field', 'member', 'status']);
        $letter = $this->letterRepository->update($id, $data);
        if ($letter) {
            return response()->json(['data' => $letter]);
        }
        return response()->json(['message' => 'Letter not found'], 404);
    }

    public function destroy($id)
    {
        if ($this->letterRepository->delete($id)) {
            return response()->json(['message' => 'Letter deleted successfully']);
        }
        return response()->json(['message' => 'Letter not found'], 404);
    }
}
