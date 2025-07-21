@props(['title', 'steps' => [], 'type' => 'info'])

<div class="alert alert-{{ $type }} alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-start">
        <div class="me-3">
            <i class="fas fa-question-circle fa-2x"></i>
        </div>
        <div class="flex-grow-1">
            <h6 class="alert-heading mb-2">
                <i class="fas fa-lightbulb"></i> {{ $title }}
            </h6>
            
            @if(!empty($steps))
                <ol class="mb-2">
                    @foreach($steps as $step)
                        <li>{{ $step }}</li>
                    @endforeach
                </ol>
            @endif
            
            {{ $slot }}
            
            <hr class="my-2">
            <small class="text-muted">
                <i class="fas fa-info-circle"></i> 
                Need help? Contact support or check our documentation.
            </small>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
