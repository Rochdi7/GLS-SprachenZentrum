<div class="table-responsive">
    <table class="table table-hover align-middle" id="pc-dt-simple">
        <thead>
            <tr>
                <th>#ID</th>
                <th>Name</th>
                <th>Slug</th>
                <th>Status</th>
                <th>Position</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse($categories as $category)
                <tr>
                    <td>{{ $category->id }}</td>
                    <td>{{ $category->getName() }}</td>
                    <td>{{ $category->slug }}</td>

                    <td>
                        @if($category->is_active)
                            <span class="badge bg-light-success text-success">Active</span>
                        @else
                            <span class="badge bg-light-danger text-danger">Disabled</span>
                        @endif
                    </td>

                    <td>{{ $category->position ?? '-' }}</td>

                    <td>{{ $category->created_at->format('Y-m-d') }}</td>

                    <td>
                        <a href="{{ route('backoffice.blog.categories.edit', $category) }}"
                           class="avtar avtar-xs btn-link-secondary me-2" title="Edit">
                            <i class="ti ti-edit f-20"></i>
                        </a>

                        <form action="{{ route('backoffice.blog.categories.destroy', $category) }}"
                              method="POST" class="d-inline-block">
                            @csrf @method('DELETE')

                            <button class="avtar avtar-xs btn-link-secondary border-0 bg-transparent p-0"
                                    onclick="return confirm('Delete this category?')" title="Delete">
                                <i class="ti ti-trash f-20"></i>
                            </button>
                        </form>
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">No categories found.</td>
                </tr>
            @endforelse
        </tbody>

    </table>
</div>
