namespace Dropblog.Services;

public interface IShareTargetService
{
    event EventHandler<SharedContentEventArgs>? SharedContentReceived;
    Task<bool> HandleSharedContentAsync(string? title, string? text, string? uri);
}

public class SharedContentEventArgs : EventArgs
{
    public string? Title { get; set; }
    public string? Text { get; set; }
    public string? Uri { get; set; }
    public string? CombinedContent { get; set; }
} 