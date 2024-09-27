@extends('admin.layouts.header')
@section('content')

<style>
    .slide-container {
        position: relative;
        overflow: hidden;
        width: 100%;
        height: 100%;
    }

    .slide-wrapper {
        display: flex;
        transition: transform 0.5s ease-in-out;
        /* Ensure smooth transition */
    }

    .slide {
        min-width: 100%;
        box-sizing: border-box;
    }

    .slide img {
        width: 100%;
        height: auto;
    }

    .prev-slide,
    .next-slide {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0, 0, 0, 0.5);
        color: #fff;
        border: none;
        padding: 10px;
        cursor: pointer;
        z-index: 10;
    }

    .prev-slide {
        left: 0;
    }

    .next-slide {
        right: 0;
    }
</style>

<form id="category-add-form" method="POST" action="{{ route('category-add') }}" enctype="multipart/form-data">
    @csrf
    <div id="category-add-modal"
        class="fixed flex inset-0 justify-center items-center bg-gray-800 bg-opacity-50 z-50 hidden">
        <div id="category-add-content" class="bg-white shadow-md max-w-full rounded">

            <div class="flex justify-between px-1 items-end space-x-6">
                <h1 class="px-2 text-xl font-bold">Add Category</h1>
                <button type="button" onclick="document.getElementById('category-add-modal').classList.add('hidden')"
                    class="text-4xl hover:text-gray-400 font-medium">&times;</button>
            </div>

            <div class="px-4">

                <div class="mt-2 flex flex-col">
                    <input type="file" name="files[]" class="file-upload-input" accept="image/*" multiple required>
                    <small class="font-bold">Note:
                        <i class="font-normal">Make sure the selected files are in "jpg", "png", or "jpeg" format.</i>
                    </small>
                </div>
                <div class="mt-2">
                    <label for="title">Title:</label>
                    <input type="text" name="title" placeholder="Title" title="Title"
                        class="block p-2 border border-gray-300 rounded w-full" required>
                </div>


                <div class="flex flex-col mt-2">
                    <label for="approval">Approval level:</label>
                    <select name="approval_level" id="" class="block p-2 border border-gray-300 rounded" required>
                        <option value="1"
                            title="Only the administrator can approve items that belong to this category.">
                            Admin only</option>
                        <option value="2" title="Only the staff can approve items that belong to this category.">Staff
                            only</option>
                        <option value="3" title="Both roles can approve items within this category.">Both</option>
                    </select>
                </div>
                <div class="flex justify-end my-2 space-x-1">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-500 text-blue-100 hover:bg-blue-800 rounded">Add</button>
                    <button type="button"
                        onclick="document.getElementById('category-add-modal').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-500 text-gray-100 hover:bg-gray-800 rounded">Close</button>
                </div>
            </div>
        </div>
    </div>
</form>


<div id="main-content" class="p-2 w-full h-full overflow-y-auto">
    <div class="flex flex-wrap gap-6">
        <!-- Add Category Button -->
        @hasrole('admin')
        <div title="Add a new category"
            class="flex flex-col bg-gray-200 rounded-lg hover:bg-gray-300 hover:shadow-inner w-52 h-52 overflow-hidden {{ $setting->transition == true ? 'transform transition-transform duration-300 hover:scale-110' : '' }}">
            <div class="flex items-center justify-center cursor-pointer hover:text-gray-800 text-gray-400 "
                onclick="document.getElementById('category-add-modal').classList.remove('hidden')">
                <h1 class="text-8xl mb-3 font-bold py-2 w-50 h-50 object-cover cursor-pointer">+</h1>
            </div>
            <div class="bg-blue-500 w-full h-full shadow-md text-center p-2 flex items-center justify-center">
                <h1 class="text-white py-2 font-bold">Add a new category</h1>
            </div>
        </div>
        @endhasrole

        @foreach ($categories as $category)
            <div id="category-{{$category->id}}"
                class="fixed inset-0 flex justify-center items-center bg-gray-900 bg-opacity-70 z-50 hidden">
                <form action="{{ route('managedCategoriesUpdate', ['category_id' => $category->id]) }}" method="POST"
                    class="bg-white rounded w-[300px]">
                    @csrf
                    <input id="isUncheckedAll" type="hidden" name="isUncheckedAll" value="false">

                    <div class="flex items-center justify-between mb-2 m-2">
                        <h1 class="text-xl font-medium">{{ $category->title }}</h1>
                        <button type="button" aria-label="Close"
                            onclick="document.getElementById('category-{{$category->id}}').classList.add('hidden')"
                            class="text-2xl font-bold text-gray-600 hover:text-gray-800">&times;</button>
                    </div>


                    <div class="flex flex-col bg-gray-100 p-2 border-2 border-b border-t">
                        <h2 class="text-medium mb-2">Users can manage:</h2>
                        @foreach ($users_for_roles as $user)
                            <div class="flex items-center mb-1">
                                <input type="checkbox" class="category-checkbox" name="users[{{$category->id}}][]"
                                    value="{{ $user->id }}" @if(isset($managedCategories[$user->id]) && in_array($category->id, $managedCategories[$user->id])) checked @endif>
                                <label class="ml-2">{{ $user->name }}</label>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex p-2 justify-end space-x-1">
                        <button class="px-4 py-2 shadow-md bg-green-200 text-green-800 hover:bg-green-400 rounded"
                            type="submit">Save</button>
                        <button tyoe="button"
                            class="px-4 py-2 shadow-md bg-gray-200 text-gray-800 hover:bg-gray-400 rounded" type="submit"
                            onclick="document.getElementById('category-{{$category->id}}').classList.add('hidden')">Cancel</button>
                    </div>

                </form>
            </div>
        @endforeach



        <!-- Category Items -->
        @foreach ($categories as $category)




                <div @hasrole('admin')
                    onclick="document.getElementById('category-{{$category->id}}').classList.remove('hidden')" @endhasrole
                    class="flex flex-col text-white rounded-lg w-52 h-52 overflow-hidden {{ $setting->transition == true ? 'transform transition-transform duration-300 hover:scale-110' : '' }}">
                    <div class="relative w-full max-w-3xl overflow-hidden slide-container">
                        <div class="slide-wrapper shadow-inner">
                            @php
                                $directory = storage_path('app/public/images/categories/' . $category->folder_name);
                                $images = array_diff(scandir($directory), array('..', '.'));
                            @endphp

                            @foreach ($images as $image)
                                <div class="slide">
                                    <img src="{{ asset('storage/images/categories/' . $category->folder_name . '/' . $image) }}"
                                        alt="Image">
                                </div>
                            @endforeach
                        </div>
                        <button class="prev-slide">&#10094;</button>
                        <button class="next-slide">&#10095;</button>
                    </div>
                    <div class="bg-blue-500 w-full h-full shadow-md text-center p-2 flex items-center justify-center">
                        <h1 class="text-lg font-semibold drop-shadow">{{ $category->title }}</h1>
                    </div>
                </div>
        @endforeach

    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.category-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', checkCheckboxes);
        });

        function checkCheckboxes() {
            const checkboxes = document.querySelectorAll('.category-checkbox');
            const allUnchecked = Array.from(checkboxes).every(checkbox => !checkbox.checked);

            if (allUnchecked) {
                document.getElementById('isUncheckedAll').value = "true";
            } else {
                document.getElementById('isUncheckedAll').value = "false";
            }
        }
    });

    document.querySelectorAll('.slide-container').forEach(slideContainer => {
        let currentIndex = 0;

        const slideWrapper = slideContainer.querySelector('.slide-wrapper');
        const slides = slideWrapper.querySelectorAll('.slide');
        const totalSlides = slides.length;

        function moveSlide(step) {
            currentIndex = (currentIndex + step + totalSlides) % totalSlides;
            const offset = -currentIndex * 100;
            slideWrapper.style.transform = `translateX(${offset}%)`;
        }


        slideContainer.querySelector('.prev-slide').addEventListener('click', () => moveSlide(-1));
        slideContainer.querySelector('.next-slide').addEventListener('click', () => moveSlide(1));
    });
</script>



@endsection