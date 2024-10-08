<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PropertyFormRequest;
use App\Models\Property;
use App\Models\Option;
use App\Models\ImageUpload;
use App\Models\UploadImageProperty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Models\User;
use Illuminate\Support\Facades\Storage;

class PropertyController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Property::class, 'property');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.properties.index', [
            //'properties' => Property::orderBy('created_at', 'desc')->withTrashed()->paginate(25)
            'properties' => Property::orderBy('created_at', 'desc')->paginate(25)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $property = new Property();
        $property->fill([
            'surface' => 40,
            'rooms' => 3,
            'bedrooms' => 1,
            'floor' => 0,
            'city' => 'Montpellier',
            'postal_code' => 34000,
            'sold' => false,
        ]);
        
        return view('admin.properties.form', [
            'property' => $property,
            'options' => Option::pluck('name', 'id'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //$property = Property::create($this->extractData(new Property(), $request));
        $property = '';
        $data = $this->validate($request, [
            'title' => ['required', 'min:8'],
            'description' => ['required', 'min:8'],
            'surface' => ['required', 'integer', 'min:10'],
            'rooms' => ['required','integer', 'min:1'],
            'bedrooms' => ['required', 'integer', 'min:0'],
            'floor' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'integer', 'min:0'],
            'city' => ['required', 'min:4'],
            'address' => ['required', 'min:8'],
            'postal_code' => ['required', 'min:3'],
            'sold' => ['required', 'boolean'],
            'options' => ['array', 'exists:options,id', 'required'],
            'image'=>'required',
            ]);
        /**
         * @var UploadedFile|null $image
         */
        if($request->hasFile('image'))
            {
                $allowedfileExtension=['pdf', 'webp','jpg', 'jpeg','png','docx'];
                $files = $request->file('image');
                //dd($files);
                foreach($files as $file){
                    $filename = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    $check=in_array($extension,$allowedfileExtension);
                    
                    if($check)
                    {
                        $property = Property::create($request->except('image'));
                        $imageName = time().'_'.uniqid().'_'.$filename;
                        $data['image'] = $file->move('images/uploads/property/'.$property->id.'/', $imageName);
                        foreach ($request->image as $img) {
                            //dd($property->images()->sync($img));

                            //$filename = $img->store('photos');
                            $img = ImageUpload::create([
                                'property_id' => $property->id,
                                'image' => $imageName,
                            ]);
                        //     UploadImageProperty::create([
                        //         'image_upload_id' => $img->id,
                        //         'property_id' => $property->id
                        //     ]);
                        }
                    }
                }
            }
        $property->options()->sync($request->validate(['options']));
        //dd($this->extractData($property, $request->image));
        
        // $image = ImageUpload::create($this->extractData($property, $request));
        
        //     'image' => $imageName,
        // ]);
        //$property->images()->sync($request->validated(('image')));
        return to_route('admin.property.index')->with('success', 'Le bien a bien été créé');
    }

     

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Property $property)
    {
        
        return view('admin.properties.form', [
            'property' => $property,
            'options' => Option::pluck('name', 'id'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Property $property)
    {
        //dd($this->extractData($property, $request));
        $property->options()->sync($request->validate(['options']));
        $property->update($this->extractData($property, $request));
        return to_route('admin.property.index')->with('success', 'Le bien a bien été modifié');
    }

    public function extractData(Property $property, Request $request): array
    {
        //$data = $request->validated();
        $data = $this->validate($request, [
            'title' => ['required', 'min:8'],
            'description' => ['required', 'min:8'],
            'surface' => ['required', 'integer', 'min:10'],
            'rooms' => ['required','integer', 'min:1'],
            'bedrooms' => ['required', 'integer', 'min:0'],
            'floor' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'integer', 'min:0'],
            'city' => ['required', 'min:4'],
            'address' => ['required', 'min:8'],
            'postal_code' => ['required', 'min:3'],
            'sold' => ['required', 'boolean'],
            'options' => ['array', 'exists:options,id', 'required'],
            'image'=>'required',
            ]);
        /**
         * @var UploadedFile|null $image
         */
        if($request->hasFile('image'))
            {
                $allowedfileExtension=['pdf','jpg', 'jpeg','png','docx'];
                $files = $request->file('image');
                //dd($files);
                foreach($files as $file){
                    $filename = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    $check=in_array($extension,$allowedfileExtension);
                    
                    if($check)
                    {
                        $imageName = time().'_'.uniqid().'_'.$filename;
                        $data['image'] = $file->move('images/uploads/property/'.$property->id, $imageName);
                        // ImageUpload::create()

                        // $items= Item::create($request->all());
                        // foreach ($request->photos as $photo) {
                        //     $filename = $photo->store('photos');
                        //     ItemDetail::create([
                        //     'item_id' => $items->id,
                        //     'filename' => $filename
                        //     ]);
                        // }
                    }
                }
            }else{
                return $data;
            }

        //$imageValidated = $request->validated('image');

        // if($imageValidated == null || $imageValidated->getError())
        // {
        //     return $data;
        // }
        // if($imageValidated){
        //     $imageName = time().'-'.uniqid().'.'.$imageValidated->getClientOriginalExtension();
        //     $data['image'] = $imageValidated->move('images/uploads/property/'.$property->id.'/', $imageName);
        // }
        return $data;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Property $property)
    {
        $property->delete();
        
        // Pour remettre le deleted_at a null et restaurer le propriété
        //$property->restore();

        // Pour supprimer veritablement
        //$property->forceDelete();
        return to_route('admin.property.index')->with('success', 'Le bien a été supprimé');
    }
}
