using System.Collections.ObjectModel;

namespace Dropblog.Services;

public interface IBlogConfigurationService
{
    ObservableCollection<BlogSite> BlogSites { get; }
    BlogSite CurrentBlogSite { get; }
    event EventHandler<BlogSite>? CurrentBlogSiteChanged;
    
    Task AddBlogSiteAsync(string name, string url);
    Task RemoveBlogSiteAsync(BlogSite blogSite);
    Task SetCurrentBlogSiteAsync(BlogSite blogSite);
    Task LoadBlogSitesAsync();
    Task SaveBlogSitesAsync();
}

public record BlogSite(int Id, string Name, string Url)
{
    public static BlogSite Empty => new(-1, "No Blog Selected", "");
} 