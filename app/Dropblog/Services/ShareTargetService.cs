namespace Dropblog.Services;

public class ShareTargetService : IShareTargetService
{
    public event EventHandler<SharedContentEventArgs>? SharedContentReceived;

    public Task<bool> HandleSharedContentAsync(string? title, string? text, string? uri)
    {
        try
        {
            // Combine the shared content intelligently
            var combinedContent = BuildCombinedContent(title, text, uri);
            
            if (string.IsNullOrWhiteSpace(combinedContent))
                return Task.FromResult(false);

            var eventArgs = new SharedContentEventArgs
            {
                Title = title,
                Text = text,
                Uri = uri,
                CombinedContent = combinedContent
            };

            SharedContentReceived?.Invoke(this, eventArgs);
            return Task.FromResult(true);
        }
        catch
        {
            return Task.FromResult(false);
        }
    }

    private static string BuildCombinedContent(string? title, string? text, string? uri)
    {
        var parts = new List<string>();

        // Add title as a header if it exists and is different from text
        if (!string.IsNullOrWhiteSpace(title) && title != text)
        {
            parts.Add($"# {title.Trim()}");
        }

        // Add text content
        if (!string.IsNullOrWhiteSpace(text))
        {
            parts.Add(text.Trim());
        }

        // Add URI as a link if it exists and wasn't already included in text
        if (!string.IsNullOrWhiteSpace(uri))
        {
            var uriToAdd = uri.Trim();
            var textContent = text?.Trim() ?? "";
            
            // Only add URI if it's not already in the text
            if (!textContent.Contains(uriToAdd))
            {
                if (Uri.TryCreate(uriToAdd, UriKind.Absolute, out var validUri))
                {
                    // Format as markdown link if we have a title, otherwise just the URL
                    if (!string.IsNullOrWhiteSpace(title) && title != text)
                    {
                        parts.Add($"[{title}]({uriToAdd})");
                    }
                    else
                    {
                        parts.Add(uriToAdd);
                    }
                }
                else
                {
                    parts.Add(uriToAdd);
                }
            }
        }

        return string.Join("\n\n", parts);
    }
} 