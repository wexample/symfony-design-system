import AppService from '../class/AppService';
import RenderDataInterface from '../interfaces/RenderData/RenderDataInterface';
import RequestOptionsInterface from '../interfaces/RequestOptions/RequestOptionsInterface';
import ComponentsService from './ComponentsService';

export default class AdaptiveService extends AppService {
  public static dependencies: typeof AppService[] = [ComponentsService];
  public static serviceName: string = 'adaptive';

  fetch(
    path: string,
    requestOptions: RequestOptionsInterface = {}
  ): Promise<any> {
    return fetch(path, {
      ...{
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
      },
      ...requestOptions,
    });
  }

  get(
    path: string,
    requestOptions: RequestOptionsInterface = {}
  ): Promise<any> {
    requestOptions.callerPage =
      requestOptions.callerPage || this.app.layout.pageFocused;

    Object.freeze(requestOptions);

    return this.fetch(path, requestOptions)
      .then((response: Response) => {
        if (!response.ok) {
          this.app.services.prompt.applicationError(
            `Error response : [${response.status}] ${response.statusText}`
          )

          return {ok: false};
        }

        // Attempt to parse the JSON response
        return response.json().then(data => {
          data.ok = true;

          // If the response is valid JSON, return the parsed data.
          return data;
        }).catch(error => {
          // If an error occurs while parsing JSON, log the error and return an empty object.
          this.app.services.prompt.applicationError("Failed to parse JSON response:", error);

          return {ok: false};
        });
      })
      .then(async (renderData: RenderDataInterface) => {
        if (renderData.ok === false) {
          return renderData;
        }

        renderData.requestOptions = requestOptions;

        // Preparing render data is executed in render node creation,
        // but at this point layout already exists,
        // so we run it manually.
        await this.app.services.layouts.prepareRenderData(renderData);

        // Wait render data loading to continue.
        return this.app.loadLayoutRenderData(renderData).then(async () => {
          // Activate every new render node.
          await this.app.layout.setNewTreeRenderNodeReady();

          return renderData;
        });
      });
  }
}
