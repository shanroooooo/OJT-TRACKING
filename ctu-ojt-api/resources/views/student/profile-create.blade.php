@extends('layouts.auth')

@section('title', 'Create Student Profile - CTU OJT Tracking')

@section('content')
<div class="max-w-2xl mx-auto py-8">
    <div class="bg-white shadow-lg rounded-lg p-6">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Create Your Student Profile</h2>
            <p class="text-gray-600 mt-2">Please complete your profile information to access the OJT tracking system.</p>
        </div>

        <form id="profileForm" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="student_id_number" class="block text-sm font-medium text-gray-700">Student ID Number</label>
                    <input type="text" id="student_id_number" name="student_id_number" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="course" class="block text-sm font-medium text-gray-700">Course</label>
                    <input type="text" id="course" name="course" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="major" class="block text-sm font-medium text-gray-700">Major (Optional)</label>
                    <input type="text" id="major" name="major"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="year_level" class="block text-sm font-medium text-gray-700">Year Level</label>
                    <select id="year_level" name="year_level" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select Year Level</option>
                        <option value="1">1st Year</option>
                        <option value="2">2nd Year</option>
                        <option value="3">3rd Year</option>
                        <option value="4">4th Year</option>
                        <option value="5">5th Year</option>
                    </select>
                </div>

                <div>
                    <label for="section" class="block text-sm font-medium text-gray-700">Section (Optional)</label>
                    <input type="text" id="section" name="section"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="required_hours" class="block text-sm font-medium text-gray-700">Required OJT Hours</label>
                    <input type="number" id="required_hours" name="required_hours" value="486" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div class="border-t pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Company Information</h3>
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="company_name" class="block text-sm font-medium text-gray-700">Company Name</label>
                        <input type="text" id="company_name" name="company_name"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="company_address" class="block text-sm font-medium text-gray-700">Company Address</label>
                        <textarea id="company_address" name="company_address" rows="3"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="supervisor_name" class="block text-sm font-medium text-gray-700">Supervisor Name</label>
                            <input type="text" id="supervisor_name" name="supervisor_name"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="supervisor_contact" class="block text-sm font-medium text-gray-700">Supervisor Contact</label>
                            <input type="text" id="supervisor_contact" name="supervisor_contact"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">OJT Schedule</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="ojt_start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" id="ojt_start_date" name="ojt_start_date"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="ojt_end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" id="ojt_end_date" name="ojt_end_date"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" onclick="window.history.back()" 
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </button>
                <button type="submit" 
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Create Profile
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Success/Error Messages -->
<div id="messageContainer" class="fixed top-4 right-4 z-50"></div>
@endsection

@push('scripts')
<script>
    document.getElementById('profileForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        
        try {
            const response = await fetch('/api/student/profile', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            
            if (response.ok) {
                showMessage('Profile created successfully! Redirecting to dashboard...', 'success');
                setTimeout(() => window.location.href = '/dashboard', 2000);
            } else {
                showMessage(result.message || 'Failed to create profile', 'error');
            }
        } catch (error) {
            showMessage('Network error. Please try again.', 'error');
        }
    });

    function showMessage(message, type) {
        const container = document.getElementById('messageContainer');
        const alertClass = type === 'success' ? 'bg-green-500' : 'bg-red-500';
        
        const messageDiv = document.createElement('div');
        messageDiv.className = `${alertClass} text-white px-6 py-4 rounded-lg shadow-lg mb-4 transform transition-all duration-300 translate-x-full`;
        messageDiv.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-3"></i>
                <span>${message}</span>
            </div>
        `;
        
        container.appendChild(messageDiv);
        
        setTimeout(() => {
            messageDiv.classList.remove('translate-x-full');
        }, 100);
        
        setTimeout(() => {
            messageDiv.classList.add('translate-x-full');
            setTimeout(() => container.removeChild(messageDiv), 300);
        }, 3000);
    }
</script>
@endpush
