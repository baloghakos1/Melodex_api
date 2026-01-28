| **URL**                                            | **HTTP Method** | **Auth** | **JSON Response**               |
| -------------------------------------------------- | --------------- | -------- | ------------------------------- |
| `/user/login`                                      | POST            | ❌        | User token                      |
| `/users`                                           | GET             | ✅        | All users                       |
| `/artists`                                         | GET             | ❌        | All artists                     |
| `/artist/{id}`                                     | GET             | ❌        | Single artist                   |
| `/artist`                                          | POST            | ✅        | New artist added                |
| `/artist/{id}`                                     | PATCH           | ✅        | Edited artist                   |
| `/artist/{id}`                                     | DELETE          | ✅        | Deleted artist                  |
| `/artist/{id}/albums`                              | GET             | ❌        | All albums of artist            |
| `/artist/{artist_id}/album/{id}`                   | GET             | ❌        | Single album of artist          |
| `/artist/{id}/album`                               | POST            | ✅        | New album added to artist       |
| `/artist/{artist_id}/album/{id}`                   | PATCH           | ✅        | Edited album of artist          |
| `/artist/{artist_id}/album/{id}`                   | DELETE          | ✅        | Deleted album                   |
| `/artist/{artist_id}/album/{id}/songs`             | GET             | ❌        | All songs of artist album       |
| `/artist/{artist_id}/album/{id}/song`              | POST            | ✅        | New song added to album         |
| `/artist/{artist_id}/album/{album_id}/song/{id}`   | PATCH           | ✅        | Edited song                     |
| `/artist/{artist_id}/album/{album_id}/song/{id}`   | DELETE          | ✅        | Deleted song                    |
| `/albums`                                          | GET             | ❌        | All albums                      |
| `/album/{id}`                                      | GET             | ❌        | Single album                    |
| `/album`                                           | POST            | ✅        | New album added                 |
| `/album/{id}`                                      | PATCH           | ✅        | Edited album                    |
| `/album/{id}`                                      | DELETE          | ✅        | Deleted album                   |
| `/album/{id}/songs`                                | GET             | ❌        | All songs of album              |
| `/album/{id}/song`                                 | POST            | ✅        | New song added to album         |
| `/album/{album_id}/song/{id}`                      | PATCH           | ✅        | Edited song                     |
| `/album/{album_id}/song/{id}`                      | DELETE          | ✅        | Deleted song                    |
| `/songs`                                           | GET             | ❌        | All songs                       |
| `/song/{id}`                                       | GET             | ✅        | Single song                     |
| `/song`                                            | POST            | ✅        | New song added                  |
| `/song/{id}`                                       | PATCH           | ✅        | Edited song                     |
| `/song/{id}`                                       | DELETE          | ✅        | Deleted song                    |
| `/playlists`                                       | GET             | ❌        | All playlists                   |
| `/playlist`                                        | POST            | ✅        | New playlist created            |
| `/playlist/{id}`                                   | PATCH           | ✅        | Edited playlist                 |
| `/playlist/{id}`                                   | DELETE          | ✅        | Deleted playlist                |
| `/playlist/{id}/songs`                             | GET             | ❌        | Songs in playlist               |
| `/playlist/{id}/song`                              | POST            | ✅        | Song added to playlist          |
| `/playlist/{playlist_id}/song/{id}`                | DELETE          | ✅        | Song removed from playlist      |
| `/user/{id}/playlists`                             | GET             | ✅        | User playlists                  |
| `/user/{id}/playlist`                              | POST            | ✅        | Playlist created for user       |
| `/user/{user_id}/playlist/{id}`                    | PATCH           | ✅        | Edited user playlist            |
| `/user/{user_id}/playlist/{id}`                    | DELETE          | ✅        | Deleted user playlist           |
| `/user/{user_id}/playlist/{id}/songs`              | GET             | ✅        | Songs in user playlist          |
| `/user/{user_id}/playlist/{id}/song`               | POST            | ✅        | Song added to user playlist     |
| `/user/{user_id}/playlist/{playlist_id}/song/{id}` | DELETE          | ✅        | Song removed from user playlist |
