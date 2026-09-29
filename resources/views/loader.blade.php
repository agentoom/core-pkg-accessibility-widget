(function() {
    const slug = '{{ $slug }}';
    const apiUrl = '{{ $apiUrl }}';

    // Prevent multiple initializations
    if (window.AgentoomAccessibility) return;
    window.AgentoomAccessibility = { slug, apiUrl };

    const script = document.createElement('script');
    script.src = `${apiUrl}/widget.js`;
    script.async = true;
    script.defer = true;
    document.head.appendChild(script);
})();
