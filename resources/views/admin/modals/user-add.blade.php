<div id="userAddModal" class="flex fixed inset-0 justify-center items-center bg-gray-800 bg-opacity-50 z-50 hidden">
    <div class="bg-white px-4 pb-2 w-64 rounded">
        <form action="{{ route('userAdd') }}" method="POST">
            @csrf
            @method('POST')
            <div class="flex justify-between items-center">
                <h1 class="text-xl font-medium">Add new user</h1>
                <button type="button" onclick="document.getElementById('userAddModal').classList.add('hidden')"
                    class="text-6xl font-thin hover:text-gray-300 focus:outline-none">&times;</button>
            </div>
            <section class="space-y-2">
                <div>
                    <label for="name">Name:</label>
                    <input type="text" name="name" placeholder="Input name"
                        class="block p-2 border border border-gray-300 w-full rounded">
                </div>

                <div>
                    <label for="name">Email:</label>
                    <input type="email" name="email" placeholder="Input email"
                        class="block p-2 border border border-gray-300 w-full rounded">
                </div>
                <div class="flex justify-end space-x-1">
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-blue-100 hover:bg-blue-800 rounded">
                        Save
                    </button>
                    <button onclick="document.getElementById('userAddModal').classList.add('hidden')" type="button"
                        class="px-4 py-2 bg-gray-500 text-gray-100 hover:bg-gray-800 rounded">
                        Cancel
                    </button>
                </div>

            </section>
        </form>


    </div>
</div>