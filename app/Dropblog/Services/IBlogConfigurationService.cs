using System.Collections.ObjectModel;

namespace Dropblog.Services;

public interface IBlogConfigurationService
{
    ObservableCollection<BlogSite> BlogSites { get; }
    BlogSite CurrentBlogSite { get; }
    event EventHandler<BlogSite>? CurrentBlogSiteChanged;
    
    Task AddBlogSiteAsync(string name, string url, string apiKey);
    Task SetApiKeyAsync(BlogSite blogSite, string apiKey);
    Task RemoveBlogSiteAsync(BlogSite blogSite);
    Task SetCurrentBlogSiteAsync(BlogSite blogSite);
    Task LoadBlogSitesAsync();
    Task SaveBlogSitesAsync();
}

public record BlogSite(int Id, string Name, string Url, string ApiKey = "")
{
    public static BlogSite Empty => new(-1, "No Blog Selected", "");
} 