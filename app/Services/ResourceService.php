// ... existing code ...

namespace App\Services;

use App\Repositories\Contracts\ResourceRepositoryInterface;
use App\Services\FileProcessorFactory;

class ResourceService
{
    public function __construct(
        protected ResourceRepositoryInterface $resourceRepository
    ) {}

    public function getResourceDetails(int $id): array
    {
        $resource = $this->resourceRepository->find($id);

        $extension = pathinfo($resource->file_path, PATHINFO_EXTENSION);
        if (str_ends_with(strtolower($resource->file_path), '.tar.gz')) {
            $extension = 'tar.gz';
        }

        $fileTree = [];
        try {
            $processor = FileProcessorFactory::make($extension);
            $fileTree = $processor->getContentsTree(storage_path('app/' . $resource->file_path));
        } catch (\Exception $e) {
            // Si falla o no se soporta vista previa, $fileTree queda vacío
        }

        return [
            'resource' => $resource,
            'fileTree' => $fileTree,
        ];
    }
}